var r = new Resumable({
    target: "upload.php",
    query: function(fileObj) {
	var folder = $(fileObj.container).data('folder') || $(fileObj.container).parent().data('folder');
        return { e: $('#current-experiment').val(), dir: $('#' + folder).val() };
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
	    var inputId = 0;
            $.map(data, function(files, folderName) {
		var folderId = 'input-' + inputId;
		inputId++;
                var input = $("<input>").attr('type', 'text').attr('id', folderId).val(folderName)
                    .change(function() {
                        $.post("rename-folder.php", { e: $('#current-experiment').val(), prev: $(this).data('val'), curr: $(this).val() });
                    });
                if (folderName == "") input.prop('disabled', true).val('Introductory .html documents');
                if (folderName == "Trash") input.prop('disabled', true);
                var ul = $("<ul>").data('folder', folderId);
                $.map(files, function(file) {
                    ul.append($("<li>").text(file));
                });
                ul.sortable({
                    connectWith: "ul",
		    dropOnEmpty: true,
                    receive: function(event, ui) {
			$.each(ui.item, function(i, item) {
                            var srcFolder = $('#' + $(ui.sender[i]).data('folder')).val();
                            var dstFolder = $('#' + $(item).parent().data('folder')).val();
                            $.post("move-item.php", { e: $('#current-experiment').val(), i: $(item).text(), s: srcFolder, d: dstFolder });
			});
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
                });
        });
}

$('#trash').click(function() {
    if (confirm("Empty the trash?"))
	$.post('empty-trash.php', { e: $('#current-experiment').val() })
	.done(function() {
	    loadExperiment($('#current-experiment').val());
	});
});

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
