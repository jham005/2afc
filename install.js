var dropDir = "";
var r = new Resumable({
    target: "upload.php",
    query: function() {
        return { e: $('#current-experiment').val(), dir: dropDir };
    },
    testChunks: true
});

$(document).on('focusin', 'input', function() {
    if (!$(this).data('val'))
	$(this).data('val', $(this).val());
});


function listExperiments(selected) {
    $.getJSON('list-experiments.php').done(function(data) {
	$('#select-experiment').empty();
	$.each(data, function(key, value) {
	    var option = $("<option/>").text(value).val(value);
	    if (value == selected)
		option.attr('selected', 'selected');
            $('#select-experiment').append(option);
	});
    });
}

function loadExperiment(experimentName) {
    $.getJSON('load-experiment.php', { e: experimentName })
        .done(function(data) {
	    $('#current-experiment').val(experimentName).data('val', experimentName);
            $('#drop-target').empty();
            $.map(data, function(files, folderName) {
                var input = $("<input>").attr('type', 'text').val(folderName)
                    .change(function() {
                        $.post("rename-folder.php", { e: $('#current-experiment').val(), prev: $(this).data('val'), curr: $(this).val() });
                    });
                if (folderName == "") input.hide();
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
                        $.post("move-item.php", { e: $('#current-experiment').val(), i: item.text(), s: srcFolder, d: dstFolder });
                    }
                });
                $('#drop-target')
		    .append($("<div>")
			    .addClass('row')
			    .append($("<div>").addClass('col-4').append(input).append($('<br>')))
			    .append($("<div>").addClass('col-8').append(ul)));
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
    var oldName = $('#current-experiment').data('val');
    var newName = $('#current-experiment').val();
    if (oldName != newName)
	$.post('rename-experiment.php', { e: oldName, n: newName })
	.done(function(sanitisedName) {
	    $('#current-experiment').val(sanitisedName).data('val', sanitisedName);
	    listExperiments(sanitisedName);
	    loadExperiment(sanitisedName);
	});
});

$('#new-experiment').click(function() {
    $.post('new-experiment.php')
	.done(function(newName) {
	    listExperiments(newName);
	    loadExperiment(newName);
	});
});

$('#new-folder').click(function() {
    var experimentName = $('#current-experiment').val();
    $.post('new-folder.php', { e: experimentName })
	.done(loadExperiment(experimentName));
});

$('#select-experiment').change(function() { loadExperiment($(this).val()); });

$('#upload-btn').click(function() { r.upload(); });

listExperiments();

$(function() { $('#select-experiment').change(); });

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
