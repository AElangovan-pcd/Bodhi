function summernoteOnImageUpload(files, editor) {
    //Resize the image that is uploaded before creating the base64 string and roates based on EXIF data.
    //Adapted from the following:
    //https://gist.github.com/geedelur/7532898
    //https://github.com/raphaelbeckmann/summernote/blob/patch-1/plugin/summernote-ext-resized-data-image.js
    $.each(files, function(idx, file) {
        var max_width = 1200;
        var max_height = 1200;

        var reader = new FileReader();

        reader.onload = function() {
            var tmpImg = new Image();
            tmpImg.src = reader.result;

            tmpImg.onload = function() {
                var tmpW = tmpImg.width;
                var tmpH = tmpImg.height;
                var canvas = document.createElement('canvas');

                EXIF.getData(file, function() {
                    // check if image is rotated by 90° or 270°
                    if (EXIF.getTag(this, 'Orientation') >= 5) {
                        tmpH = tmpImg.width;
                        tmpW = tmpImg.height;
                    }

                    if (tmpW > tmpH) {
                        if (tmpW > max_width) {
                            tmpH *= max_width / tmpW;
                            tmpW = max_width;
                        }
                    } else {
                        if (tmpH > max_height) {
                            tmpW *= max_height / tmpH;
                            tmpH = max_height;
                        }
                    }

                    canvas.width = tmpW;
                    canvas.height = tmpH;
                    var ctx = canvas.getContext('2d');

                    // transform flipped or rotated images
                    // see: http://www.daveperrett.com/articles/2012/07/28/exif-orientation-handling-is-a-ghetto/
                    switch (EXIF.getTag(this, 'Orientation')) {
                        case 8:
                            ctx.transform(0, -1, 1, 0, 0, tmpH); // rotate left
                            break;
                        case 7:
                            ctx.transform(-1, 0, 0, 1, tmpW, 0); // flip vertically
                            ctx.transform(0, -1, 1, 0, 0, tmpH); // rotate left
                            break;
                        case 6:
                            ctx.transform(0, 1, -1, 0, tmpW, 0); // rotate right
                            break;
                        case 5:
                            ctx.transform(-1, 0, 0, 1, tmpW, 0); // flip vertically
                            ctx.transform(0, 1, -1, 0, tmpW, 0); // rotate right
                            break;
                        case 4:
                            ctx.transform(1, 0, 0, -1, 0, tmpH); // flip horizontally and vertically
                            break;
                        case 3:
                            ctx.transform(-1, 0, 0, -1, tmpW, tmpH); // flip horizontally
                            break;
                        case 2:
                            ctx.transform(-1, 0, 0, 1, tmpW, 0); // flip vertically
                            break;
                        case 1:
                            ctx.transform(1, 0, 0, 1, 0, 0); // no transformation
                            break;
                        default:
                            ctx.transform(1, 0, 0, 1, 0, 0); // no transformation
                            break;
                    }

                    if (EXIF.getTag(this, 'Orientation') >= 5) {
                        ctx.drawImage(tmpImg, 0, 0, tmpH, tmpW);
                    }
                    else {
                        ctx.drawImage(tmpImg, 0, 0, tmpW, tmpH);
                    }
                    sURL = canvas.toDataURL("image/jpeg");

                    $(editor).summernote("insertImage", sURL, function ($image) {
                        $image.attr('style',"");
                        $image.attr('data-filename', file.name);
                    });
                });
            }
        }

        reader.readAsDataURL(file);

    });
}
