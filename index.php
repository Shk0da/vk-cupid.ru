<?php
session_start();
error_reporting(E_ERROR);

if (isset($_GET['exit'])) {
    session_destroy();
    header('Location: /');
}

require_once('vkapi.php');
$siteUrl = 'http://vk-cupid.ru/';
$vk = VKAPI::instance();
$authUrl = $vk->getAuthUrl();
$user = $vk->getUser();
ob_start();
header('Cache-Control: public');
header('Expires: ' . date('r', time() + 3600));
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/favicon.png" type="image/png">
    <link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/css/style.min.css">
    <title>Найди свою вторую половину ВКонтакте. Бесплатные знакомства | VK-Cupid.ru</title>
    <meta name="description"
          content="Бесплатные знакомства. Сервис для подбора идеального партнера. Поиск ВКонтакте людей, которые подойдут вам. Постройте крепкие отношения!">
    <meta name="keywords" content="найти мужа, найти жену, найти партнера, подобрать партнера, биоритмы">
</head>

<body>

<header id="top">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 col-md-12 site-title">
                <h1><a style="color: black" href="<?php echo $siteUrl; ?>">VK-Cupid.ru</a> (ВК-Купидон)</h1>
                <h2>Поиск партнера для идеальных отношений ВКонтакте</h2>
            </div>
            <div class="col-lg-4 col-md-12 main-menu">
            </div>
            <div class="col-lg-4 col-md-12 main-menu">
                <?php if ($user): ?>
                    <div class="text-center">
                        Расскажите о нас своим друзьям:
                        <a href="//vk.com/share.php?url=<?php echo $siteUrl; ?>&title=ВК Купидон&description=Бесплатный сервис для поиска второй половинки. Это не сайт знакомств, и вы точно найдёте того, кто вам подходит! Попробуйте...&image=<?php echo $siteUrl; ?>/img/bg.jpg" target="_blank">
                            <img src="/img/repost.jpg"
                                 width="100"
                                 style="margin-top: -50px;"
                                 title="Нажми на гомерчика!"
                                 alt="repost">
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <hr class="sigma-hr">
            </div>
        </div>
    </div>
</header>

<section id="home">

    <div class="container-fluid">
        <div class="row sigma-home single-page-nav">
            <div class="col-md-offset-2 col-lg-offset-2 col-lg-8 col-md-8 col-sm-12 text-center">
                <h2 style="font-size: 28px;">Найти свою вторую половинку</h2>
            </div>
            <?php if ($user): ?>

                <style>
                    .sigma-home {
                        padding-top: 35px;
                    }
                </style>

                <?php $userInfo = $vk->getUserInfo(); ?>
                <div class="row col-md-12 p-a-3">
                    <div class="col-md-3">
                        <p>
                            <img src="<?php echo $userInfo['photo_200']; ?>"><br>
                            <?php echo $userInfo['first_name']; ?> (<?php echo $userInfo['bdate']; ?>)<br>
                            <?php echo $vk->getCountry($userInfo['country']); ?>,
                            <?php echo $vk->getCity($userInfo['city']); ?><br>
                            Вы рождены в год <?php echo $vk->getAnimal(); ?><br>
                            Знак зодиака: <?php echo $vk->getZodiac(); ?>
                        </p>
                        <a href="/?exit" class="btn btn-success">Выйти</a>
                    </div>
                    <div class="col-md-9">
                        <h4>Параметры поиска: </h4>
                        <form class="form-inline allsearch" action="" method="post">
                            <input type="hidden" name="bdate"
                                   value="<?php echo date('Y-m-d', strtotime($userInfo['bdate'])); ?>">
                            <div class="form-group">
                                <select class="form-control" name="country">
                                    <?php foreach ($vk->getCountryList() as $country): ?>
                                        <option value="<?php echo $country['cid']; ?>"
                                            <?php if ($vk->getData('country') ? $vk->getData('country') == $country['cid'] : $userInfo['country'] == $country['cid']): ?> selected<?php endif; ?>>
                                            <?php echo $country['title']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="city">
                                    <?php foreach ($vk->getCityList() as $city): ?>
                                        <option value="<?php echo $city['cid']; ?>"
                                            <?php if ($vk->getData('city') ? $vk->getData('city') == $city['cid'] : $userInfo['city'] == $city['cid']): ?> selected<?php endif; ?>>
                                            <?php echo $city['title']; ?>
                                            <?php echo isset($city['area']) ? ', ' . $city['area'] : ''; ?>
                                            <?php echo isset($city['region']) ? ', ' . $city['region'] : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="sex">
                                    <?php foreach ($vk->getSexList() as $id => $sex): ?>
                                        <option value="<?php echo $id; ?>"
                                            <?php if ($vk->getData('sex') ? $vk->getData('sex') == $id : $userInfo['sex'] != $id): ?> selected<?php endif; ?>>
                                            <?php echo $sex; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>от </label>
                                <select class="form-control" name="age_from">
                                    <?php for ($i = 16; $i < 100; $i++): ?>
                                        <option value="<?php echo $i; ?>"
                                            <?php if ($vk->getData('age_from') ? $vk->getData('age_from') == $i : $vk->getAge($userInfo['bdate']) - 1 == $i): ?> selected<?php endif; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label> до </label>
                                <select class="form-control" name="age_to">
                                    <?php for ($i = 16; $i < 100; $i++): ?>
                                        <option value="<?php echo $i; ?>"
                                            <?php if ($vk->getData('age_to') ? $vk->getData('age_to') == $i : $vk->getAge($userInfo['bdate']) + 1 == $i): ?> selected<?php endif; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="status">
                                    <?php foreach ($vk->getStatusList() as $id => $status): ?>
                                        <option value="<?php echo $id; ?>"
                                            <?php if ($vk->getData('status') ? $vk->getData('status') == $id : $id == 1): ?> selected<?php endif; ?>>
                                            <?php echo $status; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="panel panel-default">
                                <div class="panel-heading">Совместимость:</div>
                                <div class="panel-body">
                                    <?php foreach ($vk->getBiorhythms() as $key => $biorhythm): ?>
                                        <label class="checkbox-inline" title="<?php echo $biorhythm['description']; ?>">
                                            <input type="checkbox" name="biorhythm[]"
                                                   value="<?php echo $key; ?>"
                                                <?php if (in_array($key, $vk->getData('biorhythm'))): ?> checked<?php endif; ?>> <?php echo $biorhythm['name']; ?>
                                        </label>
                                    <?php endforeach; ?>
                                    <label class="checkbox-inline" title="Совместимость знаков зодиака">
                                        <input type="checkbox" name="zodiak"
                                               value="1"
                                            <?php if ($vk->getData('zodiak')): ?> checked<?php endif; ?>> По гороскопу
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-warning">Найти</button>
                            </div>

                            <p>Вы также можете узнать совместимость с определенным человеком.
                                <a id="single" href="#" style="color: #f6f976;">Узнать?</a></p>
                        </form>

                        <form class="form-inline singlesearch" action="" method="post" style="display: none">
                            Укажите ссылку на профиль пользователя
                            <br>
                            <div class="row form-group">
                                <label class="sr-only" for="ids">ID пользователя</label>
                                <div class="input-group">
                                    <div class="input-group-addon">https://vk.com/</div>
                                    <input type="text"
                                           class="form-control"
                                           name="ids"
                                        <?php if ($vk->getData('ids')): ?> value="<?php echo $vk->getData('ids'); ?>" <?php endif; ?>
                                           id="ids"
                                           placeholder="id126345">
                                </div>
                                <button type="submit" class="btn btn-warning">Сравнить</button>
                            </div>
                            <br>
                            <br>
                            или укажите дату рождения человека
                            <br>
                            <div class="row form-group">
                                <label class="sr-only" for="ids">Дата рождения</label>
                                <div class="input-group">
                                    <div class="input-group-addon">Дата рождения: </div>
                                <input type="text"
                                           class="form-control date"
                                           name="date"
                                        <?php if ($vk->getData('date')): ?> value="<?php echo $vk->getData('date'); ?>" <?php endif; ?>
                                           id="ids"
                                           placeholder="14.02.1990">
                                </div>
                                <button type="submit" class="btn btn-warning">Найти</button>
                            </div>
                            <br>
                            <br>
                            <p><a href="#" id="all" style="color: #f6f976;">Искать по расширенным параметрам</a></p>
                        </form>

                        <div class="search col-md-12">
                            <?php $search = $vk->search(); ?>
                            <?php if (count($search)): ?>
                                <?php foreach ($vk->search() as $item): ?>
                                    <div class="col-sm-6 col-md-4">
                                        <div class="thumbnail">
                                            <img class="lazy"
                                                 alt="<?php echo $item['first_name'] ?>"
                                                 src=""
                                                 data-original="<?php echo $item['photo']; ?>"
                                                 data-holder-rendered="true">
                                            <div class="caption">
                                                <strong>
                                                    <a href="//vk.com/id<?php echo $item['uid']; ?>" target="_blank">
                                                        <?php echo $item['first_name'] . ' ' . $item['last_name']; ?></a>
                                                    <br>(<?php echo $item['age']; ?> лет)</strong>
                                                <p>Совместимость: <br><?php echo $item['compare']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php elseif (count($_POST)): ?>
                                Извините, но мы никого не нашли =(
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-md-offset-2 col-lg-offset-3 col-lg-6 col-md-8 col-sm-12 text-center">
                    <p>
                        Пожалуйста авторизуйтесь, чтобы мы могли начать:
                        <a href="<?php echo $authUrl; ?>" class="btn btn-success sigma-start">Войти</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>


<section id="about">

    <div class="container-fluid">
        <div class="row sigma-section-header nobg">
            <div class="col-md-offset-2 col-lg-offset-2 col-lg-8 col-md-8 col-sm-12 text-center">
                <h2 style="font-size: 28px;">О сервисе</h2>
                <p>
                    Оставить свой отзыв или предложение можно в нашей группе
                    <a style="color: #000" href="//vk.com/vk_cupid" target="_blank">http://vk.com/vk_cupid</a>
                </p>
                <p>
                    Мы предоставляем сервис бесплатно, с целью возможности людям <strong>находить своих вторых половинок
                        и строить с ними крепкие отношения</strong>.
                </p>
                <p>
                    При поиске используется специальный алгоритм который мы тестировали в течении нескольких лет.
                    Точность нашего алгоритма составляет примерно 97%!
                    <br>Хватит уже сидеть на сайтах знакомств, просто найдите того кто Вам нужен!
                </p>
            </div>
        </div>
    </div>

</section>

<footer>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <hr class="sigma-hr">
            </div>
        </div>
        <div class="row">
            <div class="sigma-copyright col-lg-8">
                <p>Copyright © 2016 VK-Cupid.ru
            </div>
            <div class="sigma-copyright col-lg-4 single-page-nav text-right">
                <p><a style="color: #000" href="#top">Наверх</a></p>
            </div>
        </div>
    </div>
</footer>

<script src="/js/jquery.min.js"></script>
<script src="/js/jquery.lazyload.min.js"></script>
<script src="/js/jquery.maskedinput.min.js"></script>
<script src="/js/app.min.js"></script>

<script>
    <?php if ($vk->getData('ids') || $vk->getData('date')): ?>
    $('.allsearch').hide();
    $('.singlesearch').show();
    <?php else: ?>
    $('.singlesearch').hide();
    $('.allsearch').show();
    <?php endif; ?>
</script>
</body>
</html>
<?php ob_end_flush(); ?>