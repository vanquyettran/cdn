<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 7/30/2017
 * Time: 1:32 PM
 */
use yii\helpers\Html;
use yii\helpers\Url;
?>
//<script>
    function imageUpload(img_select_id, img_preview_id, img_input_id) {
        var img_select = $("#" + img_select_id);
        var formatRepo = function (repo) {
            if (repo.loading) {
                return repo.text;
            }
            var markup =
                '<div class="row">' +
                '<div class="col-sm-5">' +
                '<img src="' + repo.source + '" class="img-rounded" style="width:50px" />' +
                '<b style="margin-left:5px">' + repo.name + '</b>' +
                '</div>' +
                '<div class="col-sm-3"><i class="fa fa-code-fork"></i> ' + repo.width + 'x' + repo.height + '</div>' +
                '<div class="col-sm-3"><i class="fa fa-star"></i> ' + repo.aspect_ratio + '</div>' +
                '</div>';
            return '<div style="overflow:hidden;">' + markup + '</div>';
        };
        var formatRepoSelection = function (repo) {
            return repo.name || repo.text;
        };
        img_select.select2({
            ajax: {
                url: "<?= Url::to(['/api/image/find-many']) ?>",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    // parse the results into the format expected by Select2
                    // since we are using custom formatting functions we do not need to
                    // alter the remote JSON data, except to indicate that infinite
                    // scrolling can be used
                    params.page = params.page || 1;

                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            minimumInputLength: 0,
            templateResult: formatRepo, // omitted for brevity, see the source of this page
            templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
        });

        var img_preview = document.getElementById(img_preview_id);
        var img_input = document.getElementById(img_input_id);
        img_input.addEventListener("change", function (event) {
            var file = this.files[0];
            var fd = new FormData();
            fd.append(img_input.name, file);
            fd.append('<?= Yii::$app->request->csrfParam ?>', '<?= Yii::$app->request->csrfToken ?>');
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= Url::to(['/api/image/upload-one'], true) ?>', true);
            xhr.upload.onprogress = function(event) {
                if (event.lengthComputable) {
                    var percentComplete = (event.loaded / event.total) * 100;
                    img_preview.innerHTML = percentComplete + '% uploaded';
                }
            };
            xhr.onload = function() {
                if (this.status == 200) {
                    var resp = JSON.parse(this.response);
                    console.log('Server got:', resp);
                    if (resp.success) {
                        var image = new Image();
                        image.src = resp.image.source;
                        var info = document.createElement("div");
                        info.innerHTML =resp. image.width + "x" + resp.image.height + "; " + resp.image.aspect_ratio;
                        img_preview.innerHTML = '';
                        img_preview.appendChild(image);
                        img_preview.appendChild(info);
                        img_select.empty()
                            .append('<option value="' + resp.image.id + '">' + resp.image.name + '</option>')
                            .val(resp.image.id).trigger("change");

                    } else {
                        img_preview.innerHTML = '<div class="text-danger">Errors: ' + JSON.stringify(resp.errors) + '</div>';
                    }
                } else {
                    img_preview.innerHTML = '<div class="text-danger">Upload failed! Please try again</div>';
                }
            };
            xhr.send(fd);

        });
        // On change
        img_select.on("change", function (event) {
            var id = img_select.val();
            var fd = new FormData();
            fd.append('id', id);
            fd.append('<?= Yii::$app->request->csrfParam ?>', '<?= Yii::$app->request->csrfToken ?>');
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= Url::to(['/api/image/find-one'], true) ?>', true);
            xhr.onload = function() {
                if (this.status == 200) {
                    img_preview.innerHTML = '';
                    var resp = JSON.parse(this.response);
                    console.log('Server got:', resp);
                    if (!!resp) {
                        var image = new Image();
                        image.src = resp.source;
                        img_preview.appendChild(image);
                        var info = document.createElement("div");
                        info.innerHTML = resp.width + "x" + resp.height + "; " + resp.aspect_ratio;
                        img_preview.appendChild(info);
                    } else {
                        img_preview.innerHTML = '<div class="text-danger">Cannot find this image on server</div>';
                    }
                } else {
                    img_preview.innerHTML = '<div class="text-danger">Failed to request image!</div>';
                }
            };
            xhr.send(fd);
        });
    }
