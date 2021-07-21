//   ### Manu Leyka Addon ###    //

// Dropzone 

(function ($, window, undefined) {
  // Disabling autoDiscover, otherwise Dropzone will try to attach twice.
  Dropzone.autoDiscover = false;
$(document).ready(function ($) {

    

url = frs_.ajaxurl;
      postUrl = frs_.plink;

function addVideoPlayer(vid){
        const player = new Plyr("#player01");
        const playerHTML = `<video id="player01" controls src="${vid}"></video>`;
        $('.dzvideo .dz-image').html(playerHTML);
    }

function getFileFromDB(resp,instance){
        res = JSON.parse(resp);
        if(res.status == 'ok'){
        //if(res.type == 'image'){
        const mockFile = { name: res.name, size: res.size };
        instance.options.addedfile.call(instance, mockFile);
        instance.options.thumbnail.call(instance, mockFile, res.src);
        mockFile.previewElement.classList.add('dz-success');
        mockFile.previewElement.classList.add('dz-complete');
        $('.dzimage  + input').val(res.id);
         //   }
            if(res.type == 'video'){
                addVideoPlayer(res.src);
                $('.dzvideo  + input').val(res.id);
            }
        }
        console.log("get the file: %O",res);
}

      // Image Upload     

  if ($('.dzimage.dropzone').length) {
    
          const frsDz = new Dropzone(".dzimage.dropzone", {
              url: frs_.upload,
              maxFiles: 1,
              maxFilesize: 25,
              timeout: 600000,
              chunking: true,
            forceChunking: true,
            parallelChunkUploads: false,
            chunkSize: 1024000,
            retryChunks: true,
            retryChunksLimit: 5,
            chunksUploaded: function chunksUploaded(file, done) {
                $.ajax({
                    type: 'POST',
                    url: frs_.chunks,
                    data: {
                        uuid: file['upload']['uuid'],
                        name: file['name'],
                        totalchunks: file['upload']['totalChunkCount']
                    },
                    success: function (response) {
                        if(response){
                            res = JSON.parse(response);
                            file.attachment_id = parseInt(res.id);
                        $('.dzimage  + input').val(file.attachment_id);
                        console.log('input value: %O', file.attachment_id);
                        }
                    }
                });
                file.previewElement.classList.add("dz-success");
                done();
            }, 
              acceptedFiles: 'image/*',
              dictDefaultMessage: "Перетащите изображение в это окно или нажмите, чтобы выбрать файл",
              dictCancelUpload: "Отменить загрузку",
              dictCancelUploadConfirmation: "Вы хотите отменить загрузку этого файла?",
              dictFileTooBig: "Размер этого файла ({{filesize}}Mб) больше лимита {{maxFilesize}}Mб.",
              addRemoveLinks: true,
              dictRemoveFile: "Удалить / Выбрать другой",
              thumbnailWidth: 600,
              thumbnailHeight: 400,
              init: function () {
                console.log(`post ID: ${frs_.editpost}`);
                $.ajax({
                    type: 'POST',
                    data: {post:frs_.editpost,  type:'image'},
                    url: frs_.getfile,
                    success: function(response) {getFileFromDB(response,frsDz)}
                  });  
                    
                }

          });

          frsDz.on("error", function (file, response) {
              file.previewElement.classList.add("dz-error");
          });
           
          frsDz.on('removedfile', function (file) {
            if (!this.cleaningUp) {
                var attachment_id = file.attachment_id;
                console.log("attachment_id: %O - ok", attachment_id);
                jQuery.ajax({
                    type: 'POST',
                    url: frs_.remove,
                    data: {
                        media_id: attachment_id
                    },
                    success: function (html) {
                        var res = JSON.parse(html);
                        console.log("file remove: %O", res.status);

                    }
                });
                file.attachment_id = '';
                $('.dzimage .acf-input > input').val(file.attachment_id);
                console.log('input value: %O', file.attachment_id);
                var _ref;
                if (file.previewElement) {
                    if (((_ref = file.previewElement) != null) && file.previewElement.parentNode) {
                        _ref.parentNode.removeChild(file.previewElement);
                    }
                }
                return this._updateMaxFilesReachedClass();

            }
        });
     
      }  // dropzone image

// Video Upload     

if ($('.dzvideo.dropzone').length) {
    
    const frsDzVid = new Dropzone(".dzvideo.dropzone", {
        url: frs_.upload,
        maxFiles: 1,
        maxFilesize: 250,
        timeout: 600000,
        chunking: true,
      forceChunking: true,
      parallelChunkUploads: false,
      chunkSize: 1024000,
      retryChunks: true,
      retryChunksLimit: 5,
      chunksUploaded: function chunksUploaded(file, done) {
          $.ajax({
              type: 'POST',
              url: frs_.chunks,
              data: {
                  uuid: file['upload']['uuid'],
                  name: file['name'],
                  totalchunks: file['upload']['totalChunkCount']
              },
              success: function (response) {
                if(response){
                res = JSON.parse(response);
                  file.attachment_id = parseInt(res.id);
                  $('.dzvideo + input').val(file.attachment_id);
                  console.log('input value: %O', file.attachment_id);
                  addVideoPlayer(res.url);
                }
              }
          });
          file.previewElement.classList.add("dz-success");
          done();
      }, 
        acceptedFiles: 'video/quicktime, video/mp4',
        dictDefaultMessage: "Перетащите видео в это окно или нажмите, чтобы выбрать файл",
        dictCancelUpload: "Отменить загрузку",
        dictCancelUploadConfirmation: "Вы хотите отменить загрузку этого файла?",
        dictFileTooBig: "Размер этого файла ({{filesize}}Mб) больше лимита {{maxFilesize}}Mб.",
        addRemoveLinks: true,
        dictRemoveFile: "Удалить / Выбрать другой",
        thumbnailWidth: 600,
        thumbnailHeight: 400,
        init: function () {
          
          $.ajax({
              type: 'POST',
              data: {post: frs_.editpost, type: 'video'},
              url: frs_.getfile,
              success: function(response) {getFileFromDB(response,frsDzVid)}
            });  
              
          }

    });

    frsDzVid.on("error", function (file, response) {
        file.previewElement.classList.add("dz-error");
    });
     
    frsDzVid.on('removedfile', function (file) {
      if (!this.cleaningUp) {
          var attachment_id = file.attachment_id;
          console.log("attachment_id: %O - ok", attachment_id);
          jQuery.ajax({
              type: 'POST',
              url: frs_.remove,
              data: {
                  media_id: attachment_id
              },
              success: function (html) {
                  var res = JSON.parse(html);
                  console.log("file remove: %O", res.status);

              }
          });
          file.attachment_id = '';
          $('.dzvideo .acf-input > input').val(file.attachment_id);
      console.log('input value: %O', file.attachment_id);
          var _ref;
          if (file.previewElement) {
              if (((_ref = file.previewElement) != null) && file.previewElement.parentNode) {
                  _ref.parentNode.removeChild(file.previewElement);
              }
          }
          return this._updateMaxFilesReachedClass();

      }
  });

}  // dropzone video
  }); //document ready
}(jQuery, window));
