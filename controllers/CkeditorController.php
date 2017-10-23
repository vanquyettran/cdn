<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 7/30/2017
 * Time: 11:55 AM
 */

namespace common\modules\cdn\controllers;

use Yii;
use yii\web\UploadedFile;
use yii\web\Controller;
use common\models\Image;
use common\db\MyActiveQuery;
use yii\web\NotFoundHttpException;
use common\models\Article;
use common\models\ArticleCategory;
use common\models\SeoPage;

class CkeditorController extends Controller
{
    public $layout = false;

    public function beforeAction($action)
    {
        // @TODO: Retrieve CSRF token via GET method
        $token = Yii::$app->request->get(Yii::$app->request->csrfParam);
        if (Yii::$app->request->validateCsrfToken($token)) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionUploadImage()
    {
        $module = Yii::$app->getModule('image');

        $funcNum = (string) Yii::$app->request->get('CKEditorFuncNum');
        $funcNum = preg_replace('/[^0-9]/', '', $funcNum);
//        $editor = Yii::$app->request->get('CKEditor');

        $file = UploadedFile::getInstanceByName('upload');
        $image = new Image();
        $image->active = 1;
        $image->quality = 60;
        $image->image_name_to_basename = true;
        $image->input_resize_keys = $module->params['input_resize_keys'];
        if ($image->saveFileAndModel($file)) {
            $errorMessage = '';
            $fileUrl = $image->getSource() . '?image_id=' . $image->id;
        } else {
            $errorMessage = Yii::t('app', 'Image was not uploaded') . ': ';
            foreach ($image->getErrors() as $attr => $errors) {
                $errorMessage .=
                    "\n    $attr:\n        " .
                    implode("\n        ",
                        array_map(function ($error) {
                            return str_replace('"', "'", $error);
                        }, $errors)
                    );
            }
            $fileUrl = '';
        }
        ob_start();
        ?>
        <script type="text/javascript">
            /**
             * http://docs.cksource.com/CKEditor_3.x/Developers_Guide/File_Browser_(Uploader)/Custom_File_Browser
             */
            window.parent.CKEDITOR.tools.callFunction(<?php echo json_encode($funcNum); ?>, <?php echo json_encode($fileUrl); ?>, <?php echo json_encode($errorMessage); ?>);
        </script>
        <?php
    }

    public function actionSearchModels($q = '', $page = 1, $type = null)
    {
//        if ($type === 'SeoPage') {
//            $result = [
//                'total_count' => count(SeoPage::getData()),
//                'items' => SeoPage::getData(),
//            ];
//        } else {
        /**
         * @var MyActiveQuery $query
         * @var Article|ArticleCategory|SeoPage $models[]
         */
        switch ($type) {
            case 'ArticleCategory':
                $query = ArticleCategory::find()->active();
                break;
            case 'Article':
                $query = Article::find()->active();
                break;
            case 'SeoPage':
                $query = SeoPage::find();
                break;
            default:
                throw new NotFoundHttpException();
        }

        $models = $query
            ->andWhere(['like', 'name', $q])
            ->offset($page - 1)
            ->limit(30)
            ->orderBy('SeoPage' == $type ? 'id asc' : 'id desc')
            ->all();

        $result = [
            'items' => [],
            'total_count' => $query
                ->where(['like', 'name', $q])
                ->count()
        ];

        /**
         * @var Article|ArticleCategory|SeoPage $model
         */
        foreach ($models as $model) {
            $result['items'][] = [
                'id' => $type == 'SeoPage' ? $model->url : $model->getUrl(),
                'text' => $model->name,
                'image_src' => ($type != 'SeoPage' && $image = $model->image) ? $image->getSource() : '',
                'keywords' => $model->hasAttribute('keywords')
                    ? array_map(
                        function ($item) { return trim($item); },
                        explode(',', $model->keywords)
                    )
                    : []
            ];
        }
        return json_encode($result);
    }

}