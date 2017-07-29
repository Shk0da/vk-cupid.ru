<?php

date_default_timezone_set('Europe/Moscow');
include_once('bio.php');

if (isset($_GET['type']) && $_GET['type'] == 'ajax' && $_GET['action']) {
    echo VKAPI::getAjax($_GET['action']);
    exit;
}

class VKAPI
{
    public $vk_config = [];

    private $user = null;
    private static $vk = null;

    private static $instance = null;
    private static $register = [];

    private function __clone()
    {
    }

    private function __construct()
    {
        $this->vk_config = require_once('config.php');
    }

    public static function instance()
    {
        if (empty(static::$instance)) {
            static::$instance = new VKAPI();
        }

        return static::$instance;
    }

    public static function getAjax($action)
    {
        if (method_exists(VKAPI::class, $action)) {
            return VKAPI::instance()->$action();
        }

        return null;
    }

    public function getData($key, $type = 'post')
    {
        switch ($type) {
            case 'post':
                return (isset($_POST[$key]) ? $_POST[$key] : null);
                break;
            case 'get':
                return (isset($_GET[$key]) ? $_GET[$key] : null);
                break;
            default:
                return null;
        }
    }

    public function wallRepost()
    {
        $access_token = $this->user['access_token'];

        $param = [
            'access_token' => $access_token,
            'object' => 'wall-85059539_231',
        ];

        $this->callMethod('wall.repost', $param);
    }

    public function singleSearch()
    {
        $ids = $this->getData('ids');

        $access_token = $this->user['access_token'];

        $param = [
            'access_token' => $access_token,
            'user_ids' => $ids,
            'fields' => 'country,city,photo_id,photo,sex,bdate,photo_200,photo_100',
        ];

        $search = $this->callMethod('users.get', $param)['response'];
        $result = $this->filter($search);

        return $result;
    }

    public function dateSearch($local = true)
    {
        $date = $this->getData('date');

        $access_token = $this->user['access_token'];

        $date = explode('.', $date);

        $birth_day = $date[0];
        $birth_month = $date[1];
        $birth_year = $date[2];

        if (!checkdate($birth_month, $birth_day, $birth_year))
        {
            return [];
        }

        $sex = $this->getUserInfo()['sex'] == 2 ? 1 : 2;

        $param = [
            'access_token' => $access_token,
            'birth_day' => $birth_day,
            'birth_month' => $birth_month,
            'birth_year' => $birth_year,
            'count' => 1000,
            'sort' => 0,
            'has_photo' => 1,
            'sex' => (int)$sex,
            'fields' => 'country,city,photo_id,photo,sex,bdate,photo_200,photo_100',
        ];

        if ($local) {
            $city = $this->getUserInfo()['city'];
            $country = $this->getUserInfo()['country'];
            $param['country'] = (int)$country;
            $param['city'] = (int)$city;
        }

        $search = $this->callMethod('users.search', $param)['response'];

        $result = $this->filter($search);

        if (!count($result))
        {
            return $this->dateSearch(false);
        }

        return $result;
    }

    public function search($limit = 5000)
    {
        if ($this->getData('ids')) {
            return $this->singleSearch();
        }

        if ($this->getData('date')) {
            return $this->dateSearch();
        }

        $bdate = $this->getData('bdate');
        $country = $this->getData('country');
        $city = $this->getData('city');
        $sex = $this->getData('sex');

        if (!$bdate || !$country || !$city || !$sex) return [];

        $search = [];
        for ($offset = 0; $offset <= $limit; $offset = $offset + 1000) {
            $search = array_merge($search, $this->getUsersSearch($offset));
        }

        $result = $this->filter($search);

        return $result;
    }

    public function getUsersSearch($offset = 0)
    {
        $access_token = $this->user['access_token'];
        $country = $this->getData('country');
        $city = $this->getData('city');
        $sex = $this->getData('sex');
        $age_from = $this->getData('age_from');
        $age_to = $this->getData('age_to');
        $status = $this->getData('status');

        $param = [
            'access_token' => $access_token,
            'sort' => 0,
            'count' => 1000,
            'city' => (int)$city,
            'country' => (int)$country,
            'sex' => (int)$sex,
            'age_from' => $age_from,
            'age_to' => $age_to,
            'has_photo' => 1,
            'fields' => 'country,city,photo_id,photo,sex,bdate,photo_200,photo_100',
        ];

        if ($offset) {
            $param['offset'] = $offset;
        }

        if ($status > 0) {
            $param['status'] = $status;
        }

        return $this->callMethod('users.search', $param)['response'];
    }

    public function filter($search)
    {
        $bdate = $this->getData('bdate');
        $biorhythm = $this->getData('biorhythm');
        $zodiak = $this->getData('zodiak');

        $result = [];
        foreach ($search as $item) {
            if (!isset($item['first_name'])) continue;
            if (!isset($item['photo_200'])) continue;
            if (!isset($item['bdate']) || !$item['bdate'] || strlen($item['bdate']) < 6) continue;

            $age = $this->getAge($item['bdate']);

            if ($age < 16) continue;

            $item['age'] = $age;

            if (count($biorhythm)) {
                if (!$this->boolCompare($bdate, $item['bdate'], $biorhythm)) continue;
            }

            if ($zodiak) {
                if (!$this->horoCompare($bdate, $item['bdate'])) continue;
            }

            $title = '';
            $compare = $this->compare($this->getUserInfo()['bdate'], $item['bdate'], $biorhythm);
            $rating = array_sum($compare) / count($compare);
            $item['rating'] = $rating;

            $compare = $this->compare($this->getUserInfo()['bdate'], $item['bdate']);
            foreach ($compare as $name => $val) {
                $title .= "{$name}: {$val}<br>";
            }

            $item['compare'] = $title;
            $item['photo'] = $item['photo_200'];

            $result[] = $item;
        }

        usort($result, function ($a, $b) {
            $rating_a = $a['rating'];
            $rating_b = $b['rating'];

            if ($rating_a == $rating_b) {
                return 0;
            }

            return ($rating_a > $rating_b) ? -1 : 1;
        });

        return $result;
    }

    public function horoCompare($bdate1, $bdate2)
    {
        $zodiac1 = $this->getNumZodiac($bdate1);
        $zodiac2 = $this->getNumZodiac($bdate2);

        return Bio::instance()->horoCompare($zodiac1, $zodiac2);
    }

    public function boolCompare($bdate1, $bdate2, $options = [])
    {
        return Bio::instance()->boolCompare($bdate1, $bdate2, $options);
    }

    public function compare($bdate1, $bdate2, $options = [])
    {
        return Bio::instance()->compare($bdate1, $bdate2, $options);
    }

    public function getBiorhythms()
    {
        return Bio::instance()->getBiorhythms();
    }

    public function getAge($bdate)
    {
        $birthday_timestamp = strtotime($bdate);
        $age = date('Y') - date('Y', $birthday_timestamp);
        if (date('md', $birthday_timestamp) > date('md')) {
            $age--;
        }

        return $age;
    }

    public function getCitySelect()
    {
        $view = '';

        $countryID = $this->getData('countryID');
        if (!$countryID) return $view;

        foreach ($this->getCityList($countryID) as $city) {
            $view .= "<option value=\"{$city['cid']}\">";
            $view .= $city['title'];
            $view .= isset($city['area']) ? ', ' . $city['area'] : '';
            $view .= isset($city['region']) ? ', ' . $city['region'] : '';
            $view .= '</option>';
        }

        return $view;
    }

    public function getAuthUrl()
    {
        return $this->vk()->getAuthorizeURL($this->vk_config['api_settings'], $this->vk_config['callback_url']);
    }

    public function isAuth()
    {
        return $this->vk()->isAuth();
    }

    public function getUser()
    {
        if (!isset($_SESSION['user'])) {
            try {
                if (isset($_REQUEST['code'])) {
                    $_SESSION['user'] = $this->vk()->getAccessToken($_REQUEST['code'], $this->vk_config['callback_url']);
                } else {
                    $_SESSION['user'] = null;
                }
            } catch (Exception $e) {

            }
        }

        $this->user = &$_SESSION['user'];

        return $this->user;
    }

    public function getUserInfo()
    {
        if (!isset(static::$register[__METHOD__])) {
            $access_token = $this->user['access_token'];

            static::$register[__METHOD__] = $this->callMethod('users.get', [
                'access_token' => $access_token,
                'fields' => 'country,city,photo_id,photo,sex,bdate,photo_200'
            ])['response'][0];

            $this->setAnimal(static::$register[__METHOD__]['bdate']);
            $this->setZodiac(static::$register[__METHOD__]['bdate']);
        }

        return static::$register[__METHOD__];
    }

    public function getCountry($id)
    {
        if (!isset(static::$register[__METHOD__ . $id])) {
            $access_token = $this->user['access_token'];

            static::$register[__METHOD__ . $id] = $this->callMethod('database.getCountriesById', [
                'access_token' => $access_token,
                'country_ids' => $id
            ])['response'][0]['name'];
        }

        return static::$register[__METHOD__ . $id];
    }

    public function getCity($id)
    {
        if (!isset(static::$register[__METHOD__ . $id])) {
            $access_token = $this->user['access_token'];

            static::$register[__METHOD__ . $id] = $this->callMethod('database.getCitiesById', [
                'access_token' => $access_token,
                'city_ids' => $id
            ])['response'][0]['name'];
        }

        return static::$register[__METHOD__ . $id];
    }

    public function getCountryList()
    {
        if (!isset(static::$register[__METHOD__])) {
            $access_token = $this->user['access_token'];

            static::$register[__METHOD__] = $this->callMethod('database.getCountries', [
                'access_token' => $access_token,
                'need_all' => 0,
            ])['response'];
        }

        return static::$register[__METHOD__];
    }

    public function getCityList($id = null)
    {
        $name = __METHOD__ . $id;
        $id = $this->getData('country') ?: $id;

        if (!isset(static::$register[$name]) || empty(static::$register[$name])) {
            $access_token = $this->user['access_token'];
            $country_id = $id ?: ($this->getUserInfo()['country'] ?: 1);

            $response = $this->callMethod('database.getCities', [
                'access_token' => $access_token,
                'need_all' => 0,
                'country_id' => $country_id,
            ])['response'] ?: $this->getCityList($country_id);

            static::$register[$name] = $response;
        }

        return static::$register[$name];
    }

    public function getSexList()
    {
        if (!isset(static::$register[__METHOD__])) {
            $sexList = [
                1 => 'жен',
                2 => 'муж',
            ];

            static::$register[__METHOD__] = (array)$sexList;
        }

        return static::$register[__METHOD__];
    }

    public function getStatusList()
    {
        if (!isset(static::$register[__METHOD__])) {
            $sexList = [
                0 => 'все',
                1 => 'не женат (не замужем)',
                2 => 'встречается',
                3 => 'помолвлен(-а)',
                4 => 'женат (замужем)',
                5 => 'всё сложно',
                6 => 'в активном поиске',
                7 => 'влюблен(-а)',
            ];

            static::$register[__METHOD__] = (array)$sexList;
        }

        return static::$register[__METHOD__];
    }

    public function getZodiac()
    {
        $zodiac = ['Козерог', 'Водолей', 'Рыбы', 'Овен', 'Телец', 'Близнецы', 'Рак', 'Лев', 'Девы', 'Весы', 'Скорпион', 'Стрелец'];
        return $zodiac[$this->user['zodiac']];
    }

    public function getAnimal()
    {
        $animals = ['Крысы', 'Быка', 'Тигра', 'Кролика', 'Дракона', 'Змеи', 'Лошади', 'Овцы', 'Обезьяны', 'Петуха', 'Собаки', 'Кабана'];
        return $animals[$this->user['animal']];
    }

    public function getNumZodiac($bdate)
    {
        $birthday = date('Y-m-d', strtotime($bdate));
        $birthday = array_reverse(explode("-", $birthday));
        $day = $birthday[0];
        $month = $birthday[1];
        $signsstart = [1 => 21, 2 => 20, 3 => 20, 4 => 20, 5 => 20, 6 => 20, 7 => 21, 8 => 22, 9 => 23, 10 => 23, 11 => 23, 12 => 23, 13 => 21];
        $znak = $day < $signsstart[$month + 1] ? $month - 1 : $month % 12;

        return $znak;
    }

    public function setZodiac($bdate)
    {
        $znak = $this->getNumZodiac($bdate);
        $this->user['zodiac'] = $znak;
    }

    public function setAnimal($bdate)
    {
        $birthday = date('Y-m-d', strtotime($bdate));
        $year = str_replace("-", "", substr($birthday, 0, 4));
        $zodiac = 1;
        $count = 1;
        for ($i = 1900; $i < $year; $i++) {
            $zodiac = $count++;
            if ($count == 12) $count = 0;
        }

        $this->user['animal'] = $zodiac;
    }

    protected function vk()
    {
        if (empty(static::$vk)) {
            require_once('vk/src/VK/VK.php');
            require_once('vk/src/VK/VKException.php');
            static::$vk = new VK\VK($this->vk_config['app_id'], $this->vk_config['api_secret']);
        }

        return static::$vk;
    }

    protected function callMethod($method, $param)
    {
        $this->vk()->api('stats.trackVisitor', []);
        return $this->vk()->api($method, $param);
    }

}
