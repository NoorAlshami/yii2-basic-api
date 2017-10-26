<?php

namespace app\modules\v1\controllers;

use app\models\LoginForm;
use app\models\Note;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

use yii\web\ForbiddenHttpException;

use app\models\User;


class UserController extends Controller
{
    public $modelClass='app\models\user';

   public function behaviors()
	{
		$behaviors=parent::behaviors();
		$behaviors['authenticator']=[
		    'class'=>httpBearerAuth::className(),

            'only'=>['index','view']

            ];

        return $behaviors;
	}


    public function actionLogin()
    {
        $post = Yii::$app->request->post();
        $model = User::findOne(["username" => $post["username"]]);
        if (empty($model))
        {
            throw new NotFoundHttpException('User not found');
        }
        if ($model->validatePassword($post["password"]))
        {

            return $model->access_token;
        }
        else
        {
            throw new ForbiddenHttpException();
        }
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['view']);

        return $actions;
    }

    public function actionIndex(){
        return new ActiveDataProvider([
            'query' =>  User::find(),
            'pagination' => [
                'pageSize' => 19,
            ],
        ]);

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



   public function actionGet(){
       $user = User::findIdentityByAccessToken($this->getBearerToken());
       if($user){
           return $user;
       }else {
           throw new NotFoundHttpException("Object not found");
       }

   }
    private function add($params)
    {
        $params['username'];
        $user = User::findOne(['username' => $params['username']]);

        if (!$user) {
            $user = new User();
            $user->username = $params['username'];
            $user->password = $params['password'];
            $user->save();
        }

        return $user;
    }

    public function actionRegister()
    {
        return $this->add(Yii::$app->request->getBodyParams());

    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return 'Logout';
    }





}
