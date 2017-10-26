<?php

namespace app\modules\v1\controllers;

use app\models\Note;
use app\models\User;
use Yii;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class NoteController extends ActiveController
{
    public $modelClass = 'app\models\Note';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => httpBearerAuth::className(),

        ];

        return $behaviors;
    }

    function getBearerToken()
    {
        $headers = Yii::$app->request->headers['authorization'];
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);

        return $actions;
    }

    public function actionCreate()
    {
        $user = User::findIdentityByAccessToken($this->getBearerToken());

        $note = new Note();
        $note->load(Yii::$app->request->post(), '');
        $note->user_id = $user->id;
        $note->save();

        return $note;

    }

}