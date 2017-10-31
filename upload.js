var dropDir = "";
var r = new Resumable({
    target: "upload.php",
    query: function() {
	return {e: $('#experiment').val(), dir: dropDir};
    },
    testChunks: true
});

$(document).on('focusin', 'input', function(){
    $(this).data('val', $(this).val());
});

$('#experiment').change(function() {
    $.get('load-experiment.php', {e: $(this).val()}, null, "json")
	.done(function(data) {
	    $('#drop-target').empty();
	    $.map(data, function(files, folderName) {
		var div = $("<div>");
		var input = $("<input type='text' value='" + folderName + "'/>")
		    .change(function() {
			$.post("rename.php", {e: $('#experiment').val(), prev: $(this).data('val'), curr: $(this).val()});
		    });
		if (folderName == "") input.hide();
		div.append(input);
		var ul = $("<ul>");
		$.map(files, function(file) {
		    ul.append($("<li>").text(file));
		});
		ul.sortable({
		    connectWith: "ul",
		    receive: function(event, ui) {
			var item = $(ui.item[0]);
			var srcFolder = $(ui.sender[0]).parent('div').find('input').val();
			var dstFolder = item.parent().parent().find('input').val();
			$.post("move.php", {e: $('#experiment').val(), item: item.text(), srcFolder: srcFolder, dstFolder: dstFolder});
		    }
		});
		div.append(ul);
		$('#drop-target').append(div);
	    });
	    r.assignDrop($('#drop-target ul'));
	    $('#drop-target ul')
		.on('dragenter', function() { $(this).addClass('resumable-dragover'); })
		.on('dragend', function() { $(this).removeClass('resumable-dragover'); })
		.on('drop', function(event) {
		    $(this).removeClass('resumable-dragover')
		    dropDir = $(event.target).parent().parent().find('input').val();
		});
	});
});

$('#upload-btn').click(function() { r.upload(); });
 
var progressBar = new ProgressBar("#upload-progress");
 
r.on("fileAdded", function(file, event) {
    $(event.target).parent().append($('<li>').text(file.fileName).css("opacity", "0.3"));
    progressBar.fileAdded();
});

r.on("fileSuccess", function(file, message) {
    $('#drop-target li').filter(function() { return $(this).text() == file.fileName; }).css("opacity", "1");
    progressBar.finish();
});
 
r.on("progress", function() {
    progressBar.uploading(r.progress() * 100);
});

function ProgressBar(ele) {
    this.thisEle = $(ele);
    
    this.fileAdded = function() {
        (this.thisEle).removeClass("hide").find(".progress-bar").css("width", "0%");
    },
 
    this.uploading = function(progress) {
        (this.thisEle).find(".progress-bar").attr("style", "width:" + progress + "%");
    },
    
    this.finish = function() {
        (this.thisEle).addClass("hide").find(".progress-bar").css("width", "0%");
    }
}
