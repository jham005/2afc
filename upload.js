var r = new Resumable({
    target: "upload.php",
    testChunks: true
});
 
r.assignBrowse(document.getElementById("add-file-btn"), true);
r.assignDrop(document.getElementById('drop-target'));
$('#drop-target')
    .on('dragenter', function() { $(this).addClass('resumable-dragover'); })
    .on('dragend', function() { $(this).removeClass('resumable-dragover'); })
    .on('drop', function() { $(this).removeClass('resumable-dragover'); });

$("#start-upload-btn").click(function() {
    r.upload();
});
 
$("#pause-upload-btn").click(function() {
    if (r.files.length > 0) {
        if (r.isUploading())
            return r.pause();
        return r.upload();
    }
});
 
var progressBar = new ProgressBar($("#upload-progress"));
 
r.on("fileAdded", function(file, event) {
    $('#drop-target ul').append($('<li>').text(file.fileName));
    progressBar.fileAdded();
});

r.on("fileSuccess", function(file, message) {
    $('#drop-target ul li').filter(function() { return $(this).text() == file.fileName; }).remove();
    progressBar.finish();
});
 
r.on("progress", function() {
    progressBar.uploading(r.progress() * 100);
    $("#pause-upload-btn").find(".glyphicon").removeClass("glyphicon-play").addClass("glyphicon-pause");
});

r.on("pause", function() {
    $("#pause-upload-btn").find(".glyphicon").removeClass("glyphicon-pause").addClass("glyphicon-play");
});

function ProgressBar(ele) {
    this.thisEle = $(ele);
    
    this.fileAdded = function() {
        (this.thisEle).removeClass("hide").find(".progress-bar").css("width", "0%");
    },
 
    this.uploading = function(progress) {
        (this.thisEle).find(".progress-bar").attr("style", "width:"+progress+"%");
    },
    
    this.finish = function() {
        (this.thisEle).addClass("hide").find(".progress-bar").css("width", "0%");
    }
}
