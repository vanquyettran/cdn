<?php
use yii\helpers\Url;
?>
//<script>
function ckeditor(id) {
    /**
     * Documentation:
     * http://sdk.ckeditor.com/samples/fileupload.html
     * http://docs.cksource.com/CKEditor_3.x/Developers_Guide/File_Browser_(Uploader)
     */
    CKEDITOR.timestamp = Math.floor(new Date() / 1800000);
    return CKEDITOR.replace(id, {
        extraPlugins: 'uploadimage',
        height: 300,

        linkselectorAjaxUrls: {
            searchModels: '<?= Url::to(['/cdn/ckeditor/search-models']) ?>'
        },

        linkselectorModelTypes: [
            ['Seo Page', 'SeoPage', 'default'],
            ['Article', 'Article'],
            ['Article Category', 'ArticleCategory']
        ],

        // Configure your file manager integration. This example uses CKFinder 3 for PHP.
//    filebrowserBrowseUrl: '/ckfinder/ckfinder.html',
//        filebrowserImageBrowseUrl: '/ckfinder/ckfinder.html?type=Images',
        filebrowserImageBrowseUrl: '<?= Yii::getAlias('@web/libs/ckfinder/ckfinder.html?type=Images') ?>',
        filebrowserUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
        filebrowserImageUploadUrl: '<?php echo Url::to([
            '/cdn/ckeditor/upload-image',
            Yii::$app->request->csrfParam => Yii::$app->request->csrfToken
        ]) ?>',

        // The following options are not necessary and are used here for presentation purposes only.
        // They configure the Styles drop-down list and widgets to use classes.

        stylesSet: [
            {name: 'Narrow image', type: 'widget', widget: 'image', attributes: {'class': 'image-narrow'}},
            {name: 'Wide image', type: 'widget', widget: 'image', attributes: {'class': 'image-wide'}}
        ],

        // Load the default contents.css file plus customizations for this sample.
        contentsCss: [CKEDITOR.basePath + 'contents.css', 'http://sdk.ckeditor.com/samples/assets/css/widgetstyles.css'],

        // Configure the Enhanced Image plugin to use classes instead of styles and to disable the
        // resizer (because image size is controlled by widget styles or the image takes maximum
        // 100% of the editor width).
        image2_alignClasses: ['image-align-left', 'image-align-center', 'image-align-right'],
        image2_disableResizer: true
    });
}
