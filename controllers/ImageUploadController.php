<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 7/30/2017
 * Time: 1:31 PM
 */

namespace common\modules\cdn\controllers;


use yii\web\Controller;

class ImageUploadController extends Controller
{
    public $layout = false;
    public function actionIndex()
    {
        return $this->render('index');
    }
}