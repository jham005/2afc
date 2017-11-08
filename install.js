var dropDir = "";
var r = new Resumable({
    target: "upload.php",
    query: function() {
        return {e: $('#current-experiment').val(), dir: dropDir};
    },
    testChunks: true
});

$(document).on('focusin', 'input', function() {
    if (!$(this).data('val'))
	$(this).data('val', $(this).val());
});


function listExperiments() {
    $.getJSON('list-experiments.php').done(function(data) {
	var curr = $('#select-experiment').val();
	var currStillExists = false;
	$('#select-experiment').empty();
	$.each(data, function(key, value) {
            $('#select-experiment').append($("<option/>").text(value).val(value));
	    if (value == curr) currStillExists = true;
	});
	if (currStillExists)
	    $('#select-experiment').val(curr);
	$('#select-experiment').change();
    });
}

function loadExperiment(experiment) {
    $.getJSON('load-experiment.php', {e: experiment})
        .done(function(data) {
	    var e = $('#select-experiment').val();
	    $('#current-experiment').val(e).data('val', e).change();
            $('#drop-target').empty();
            $.map(data, function(files, folderName) {
                var input = $("<input>").attr('type', 'text').val(folderName)
                    .change(function() {
                        $.post("rename-folder.php", {e: $('#current-experiment').val(), prev: $(this).data('val'), curr: $(this).val()});
                    });
                if (folderName == "") input.prop('disabled', true);
                var ul = $("<ul>");
                $.map(files, function(file) {
                    ul.append($("<li>").text(file));
                });
                ul.sortable({
                    connectWith: "ul",
		    dropOnEmpty: true,
                    receive: function(event, ui) {
                        var item = $(ui.item[0]);
                        var srcFolder = $(ui.sender[0]).parent().parent().find('input').val();
                        var dstFolder = item.parent().parent().parent().find('input').val();
                        $.post("move-item.php", {e: $('#current-experiment').val(), i: item.text(), s: srcFolder, d: dstFolder});
                    }
                });
                $('#drop-target')
		    .append($("<div>")
			    .addClass('row')
			    .append($("<div>").addClass('col-2').append(input).append($('<br>')))
			    .append($("<div>").addClass('col-6').append(ul)));
            });
            r.assignDrop($('#drop-target ul'));
            $('#drop-target ul')
                .on('dragenter', function() { $(this).addClass('resumable-dragover'); })
                .on('dragend', function() { $(this).removeClass('resumable-dragover'); })
                .on('drop', function(event) {
                    $(this).removeClass('resumable-dragover')
                    dropDir = $(event.target).parent().parent().parent().find('input').val();
                });
        });
}

$('#current-experiment').change(function() {
    var isChanged = $(this).val() != $(this).data('val');
    $('#rename-experiment').prop('disabled', !isChanged);
    $('#new-experiment').prop('disabled', !isChanged);
    $('#new-folder').prop('disabled', isChanged);
});


$('#rename-experiment').click(function() {
    $.post('rename-experiment.php', {e: $('#current-experiment').data('val'), n: $('#current-experiment').val()})
	.done(listExperiments);
});

$('#new-experiment').click(function() {
    $.post('new-experiment.php', {n: $('#current-experiment').val()})
	.done(listExperiments);
});

$('#new-folder').click(function() {
    $.post('new-folder.php', {e: $('#current-experiment').val() })
	.done(loadExperiment($('#current-experiment').val()));
});

$('#select-experiment').change(function() { loadExperiment($(this).val()); });

listExperiments();

$('#upload-btn').click(function() { r.upload(); });
 
var progressBar = new ProgressBar("#upload-progress");
 
r.on("fileAdded", function(file, event) {
    $(event.target).append($('<li>').text(file.fileName).css("opacity", "0.3"));
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
