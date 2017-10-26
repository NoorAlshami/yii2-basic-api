<?php


namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;



/**

 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $access_token
 *
 * @property AccessToken[] $accessTokens
 * @property Note[] $notes

 */

class User extends ActiveRecord implements identityInterface
{

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['username'], 'string', 'max' => 50],
            [['email', 'password'], 'string', 'max' => 100],
            [['access_token'], 'string', 'max' => 255],
        ];
    }
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'access_token' => 'Access Token',
        ];
    }
    public function getAccessTokens()
    {
        return $this->hasMany(AccessToken::className(), ['user_id' => 'id']);
    }
    public function getNotes()
    {
        return $this->hasMany(Note::className(), ['user_id' => 'id']);
    }
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return $this->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);

    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->access_token = Yii::$app->getSecurity()->generateRandomString();
            }
            return true;
        }

        return false;
    }

    public function fields()
    {
        return ['id', 'username'];
    }

    public function extraFields()
    {
        return ['notes'];
    }
}