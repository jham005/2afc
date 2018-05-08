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


function listExperiments() {
    $("#loading-dialog").modal();
    $.getJSON('list-experiments.php').done(function(data) {
	$("#loading-dialog").modal('hide');
	$('#select-experiment').empty();
	$.each(data, function(key, value) {
            $('#select-experiment').append($("<option/>").text(value).val(value));
	});
    });
}

function loadExperiment(experimentName) {
    $("#loading-dialog").modal();
    $.getJSON('load-experiment.php', { e: experimentName })
        .done(function(data) {
	    $("#loading-dialog").modal('hide');
	    $('#select-row').hide();
	    $('#edit-row').show();
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
                if (folderName == "") input.prop('disabled', true).attr('title', 'Shared tutorial and instructions html files');
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
			    var srcTotal = $(ui.sender).closest('ul').parent().next().find('.folder-total');
			    srcTotal.text((parseInt(srcTotal.text()) - 1) + " item(s)");
			    var dstTotal = $(item).closest('ul').parent().next().find('.folder-total');
			    dstTotal.text((parseInt(dstTotal.text()) + 1) + " item(s)");
			});
                    }
                });
                $('#drop-target')
		    .append($("<div>")
			    .addClass('row')
			    .append($("<div>").addClass('col-3').append(input).append($('<br>')))
			    .append($("<div>").addClass('col-6').append(ul))
			    .append($("<div>")
				    .addClass('col-3')
				    .append($('<span class="folder-total">').text(files.length + " item(s)"))));
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

$('#change-experiment').click(function() {
    $('#select-row').show();
    $('#edit-row').hide();
});

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
	    listExperiments();
	    loadExperiment(sanitisedName);
	});
});

$('#new-experiment').click(function() {
    $.post('new-experiment.php')
	.done(function(newName) {
	    listExperiments();
	    loadExperiment(newName);
	});
});

$('#new-folder').click(function() {
    var experimentName = $('#current-experiment').val();
    $("#loading-dialog").modal();
    $.post('new-folder.php', { e: experimentName })
	.done(function() {
	    $("#loading-dialog").modal('hide');
	    loadExperiment(experimentName);
	});
});

$('#go-experiment').click(function() { loadExperiment($('#select-experiment').val()); });

$('#select-row').show();
$('#edit-row').hide();
listExperiments();

var progressBar = new ProgressBar("#upload-progress");
 
r.on("fileAdded", function(file, event) {
    $(event.target).closest('ul').append($('<li>').text(file.fileName).css("opacity", "0.3"));
    progressBar.fileAdded();
    r.upload();
});

r.on("fileSuccess", function(file, message) {
    var ul = $(file.container).closest('ul');
    var li = ul.children().filter(function() { return $(this).text() == file.fileName; });
    li.css("opacity", "1");
    var total = ul.parent().next().find('.folder-total');
    total.text((parseInt(total.text()) + 1) + " item(s)");
    progressBar.finish();
    r.removeFile(file);
    r.upload();
});
 
r.on("progress", function() {
    progressBar.uploading(r.progress() * 100);
});

function ProgressBar(ele) {
    this.ele = $(ele);
    
    this.fileAdded = function() {
        this.ele.removeClass("hide").find(".progress-bar").css("width", "0%");
    },
 
    this.uploading = function(progress) {
        this.ele.find(".progress-bar").attr("style", "width:" + progress + "%");
    },
    
    this.finish = function() {
        this.ele.addClass("hide").find(".progress-bar").css("width", "0%");
    }
}
