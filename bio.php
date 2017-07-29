<?php


class Bio
{
    protected $biorhythms;
    protected static $_instance;
    protected static $register = [];

    public static function instance()
    {
        if (empty(static::$_instance)) {
            static::$_instance = new Bio();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        /*
            Физический — 23,6884 суток — соответствует нижней чакре Муладхара
            Эмоциональный — 28,426125 суток — вторая чакра Свадхистана
            Интеллектуальный — 33,163812 суток — третья чакра Манипура
            Сердечный — 37,901499 суток — четвертая чакра Анахата
            Творческий — 42,6392 суток — пятая чакра Вишудха
            Интуитивный — 47,3769 суток — шестая чакра Аджна
            Высшая чакра — 52,1146 суток — седьмая чакра Сахасрара
         */
        $this->biorhythms = [
            'fiz' => [
                'name' => 'Физическая',
                'cycle' => 23.6884,
                'description' => "Физическое притяжение. \nКачество секса.",
            ],
            'emo' => [
                'name' => 'Эмоциональная',
                'cycle' => 28.426125,
                'description' => "Интеллектуальная совместимость с женщиной может стать для мужчины серьезным \nстимулом для ее завоевания. Она делает женщину в глазах мужчины, как минимум, интересной \nсобеседницей. А ведь для мужчины умная женщина очень притягательна.",
            ],
            'int' => [
                'name' => 'Интелектуальная',
                'cycle' => 33.163812,
                'description' => "Это та самая искра, чувства с первого взгляда и \nрадость быть вместе, разделяя общие эмоции.\nОна дает основу для отношений между мужчиной и женщиной",
            ],
        ];
        /*
           Огонь — Овен, Лев, Стрелец
           Воздух — Близнецы, Весы, Водолей
           Земля — Телец, Дева, Козерог
           Вода — Рак, Скорпион, Рыба
        */
        // 'Козерог', 'Водолей', 'Рыбы', 'Овен', 'Телец', 'Близнецы', 'Рак', 'Лев', 'Девы', 'Весы', 'Скорпион', 'Стрелец'
        /*
          3 правила совместимости знаков Зодиака:
            знаки не одинаковы и знаки принадлежат одной стихии
             или
            знак Земли — со знаком Воды / знак Воздуха — со знаком Огня
        */
        $this->horo = [
            'fire' => [4, 8, 12],
            'air' => [6, 10, 2],
            'earth' => [5, 9, 1],
            'water' => [7, 11, 3],
        ];
    }

    public function horoCompare($zodiac1, $zodiac2)
    {
        $name = md5('horo' . $zodiac1 . $zodiac2);
        if (!isset(static::$register[$name])) {
            $compare = false;
            $horo = false;

            foreach ($this->horo as $zodiacs) {
                if (in_array($zodiac1, $zodiacs) && in_array($zodiac2, $zodiacs)) {
                    $horo = true;
                }
            }

            if ($zodiac1 <> $zodiac2 && $horo) {
                $compare = true;
            }

            if (in_array($zodiac1, $this->horo['earth']) && in_array($zodiac2, $this->horo['water'])) {
                $compare = true;
            }
            if (in_array($zodiac2, $this->horo['earth']) && in_array($zodiac1, $this->horo['water'])) {
                $compare = true;
            }
            if (in_array($zodiac1, $this->horo['fire']) && in_array($zodiac2, $this->horo['air'])) {
                $compare = true;
            }
            if (in_array($zodiac2, $this->horo['fire']) && in_array($zodiac1, $this->horo['air'])) {
                $compare = true;
            }

            static::$register[$name] = $compare;
        }

        return static::$register[$name];
    }

    public function getRhythms($bdate)
    {
        $rhythms = [];
        if (!$bdate) return $rhythms;

        $biorhythms = $this->biorhythms;
        $lived_days = (new \DateTime($bdate))
            ->diff(new \DateTime())
            ->days;

        foreach ($biorhythms as $biorhythm) {
            $rhythms[$biorhythm['name']] = (sin(2 * pi() * $lived_days / $biorhythm['cycle'])) * 100;
        }

        return $rhythms;
    }

    public function compare($bdate1, $bdate2, $options = null)
    {
        $name = md5('compare' . $bdate1 . $bdate2 . implode('', $options));
        if (!isset(static::$register[$name])) {
            $compare = [];

            $biorhythms = $this->biorhythms;
            $diff_lived_days = (new \DateTime($bdate1))
                ->diff(new \DateTime($bdate2))
                ->days;

            foreach ($biorhythms as $biorhythm) {
                $rhythm = (int)floor((($diff_lived_days / $biorhythm['cycle']) - floor($diff_lived_days / $biorhythm['cycle'])) * 100);
                $compare[$biorhythm['name']] = ($rhythm > 50) ? (($rhythm - 50) * 2) : (-1) * (($rhythm - 50) * 2);
            }

            if ($options && is_array($options)) {
                $select_compare = [];
                foreach ($options as $select) {
                    $select = $this->biorhythms[$select];
                    $select_compare[$select['name']] = $compare[$select['name']];
                }

                static::$register[$name] = $select_compare;
                return static::$register[$name];
            }

            static::$register[$name] = $compare;
        }

        return static::$register[$name];
    }

    public function boolCompare($bdate1, $bdate2, $options = null)
    {
        $name = md5('boolCompare' . $bdate1 . $bdate2 . implode('', $options));
        if (!isset(static::$register[$name])) {
            static::$register[$name] = false;
            $compare = $this->compare($bdate1, $bdate2, $options);
            $average = array_sum($compare) / count($compare);

            if ($average >= 60) {
                static::$register[$name] = true;
            }
        }

        return static::$register[$name];
    }

    public function getBiorhythms()
    {
        return $this->biorhythms;
    }
}
