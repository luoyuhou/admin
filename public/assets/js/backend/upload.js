/**
 * Created by 简简单单 on 2020/10/18.
 */
define(['jquery'], function ($) {
    return  function(self, input, url, preview) {
        console.log('preview', preview);
        var parent = $(self).parent();
        parent.append('<input type="file" class="hidden" id="upload-hidden" />');
        var inputEle = $('#upload-hidden');
        inputEle.click();
        inputEle.on('change', function () {
            var file = $(this)[0].files[0];
            var imgPath = $(this)[0].value;
            var extn = imgPath.substring(imgPath.lastIndexOf(".")+1).toLowerCase();
            inputEle.remove();
            var flag = true;
            if (preview) {
                if(extn == "gif" || extn == "png" || extn == "jpeg" || extn == "jpg"){
                    if(typeof (FileReader) != "undefined"){
                        var reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = function (ev) {
                            preview.attr('src', ev.target.result);
                        };
                    }else {
                        alert("FileReader 不支持！")
                    }
                }else{
                    flag = false;
                    alert("请选择图像文件")
                }
            }

            if (flag) {
                var formData = new FormData();
                formData.append('file', file);
                $.ajax({
                    url: url + '/upload',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (!res.errno) {
                            input.val(res.data);
                        }
                    }
                });
            }
        });
    };
});

