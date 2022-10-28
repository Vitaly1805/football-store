<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/nouislider.min.css">
    <link rel="stylesheet" href="../css/iconsfont.css">
    <link rel="stylesheet" href="../css/style.min.css">
    <link rel="shortcut icon" href="../images/favicon.ico" type="image/png">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <title>Football Life</title>
</head>

<body>
<div class="modal-overlay-reg-auth">
    <div class="modal-reg-auth">
        <a class="close-modal-reg-auth">
            <svg viewBox="0 0 20 20">
                <path fill="#000000"
                      d="M15.898,4.045c-0.271-0.272-0.713-0.272-0.986,0l-4.71,4.711L5.493,4.045c-0.272-0.272-0.714-0.272-0.986,0s-0.272,0.714,0,0.986l4.709,4.711l-4.71,4.711c-0.272,0.271-0.272,0.713,0,0.986c0.136,0.136,0.314,0.203,0.492,0.203c0.179,0,0.357-0.067,0.493-0.203l4.711-4.711l4.71,4.711c0.137,0.136,0.314,0.203,0.494,0.203c0.178,0,0.355-0.067,0.492-0.203c0.273-0.273,0.273-0.715,0-0.986l-4.711-4.711l4.711-4.711C16.172,4.759,16.172,4.317,15.898,4.045z">
                </path>
            </svg>
        </a>
        <div class="modal-content">
            <div class="modal-content__body">
                <div class="modal-content__reg-auth">
                    <div class="modal-content__title modal-content__title_active">
                        Вход
                    </div>
                    <div class="modal-content__title">
                        Регистрация
                    </div>
                </div>
                <form action="" method="POST" class="modal-content__form modal-content__form_active">
                    <input type="text" class="modal-content__input" placeholder="E-mail или номер телефона" name="authorization-emailorphone" required pattern="^([a-zA-Z0-9]*@(gmail|mail)\.(com|ru))|([0-9]{11})$">
                    <div class="password">
                        <input type="password" id="password-input-in" class="modal-content__input" placeholder="Пароль"
                               name="authorization-password" required>
                        <a href="#" class="password-control"
                           onclick="return show_hide_password(this, 'password-input-in');"></a>
                    </div>
                    <div class="modal-content__checkbox">
                        <section class="sidebar-check">
                            <input type="checkbox" id="modal-check" name="authorization-memory">
                            <label for="modal-check">
                                <span></span>
                                Запомнить меня
                            </label>
                        </section>
                    </div>
                    <input type="submit" value="Войти" class="modal-content__input modal-content__button" name="authorization">
                    <?php if(isset($_SESSION['authorization'])): ?>
                    <span style="font-size: 18px;display: inline-block;margin: 20px auto 0 auto;"><?= $_SESSION['authorization'] ?></span>
                    <?php endif; unset($_SESSION['authorization'] )?>
                </form>

                <form action="" method="POST" class="modal-content__form">
                    <input type="text" class="modal-content__input" placeholder="Имя*" required>
                    <input type="text" class="modal-content__input" placeholder="Фамилия*" required>
                    <input type="text" class="modal-content__input" placeholder="Отчество">
                    <input type="number" class="modal-content__input" placeholder="Номер телефона*" required>
                    <input type="email" class="modal-content__input" placeholder="E-mail*" required>
                    <div class="password password-reg">
                        <input type="password" id="password-input-reg" class="modal-content__input" placeholder="Пароль"
                               name="password" required>
                        <a href="#" class="password-control"
                           onclick="return show_hide_password(this, 'password-input-reg');"></a>
                    </div>
                    <div class="password">
                        <input type="password" id="password-input-conf" class="modal-content__input"
                               placeholder="Подтвердите пароль" name="password" required>
                        <a href="#" class="password-control"
                           onclick="return show_hide_password(this, 'password-input-conf');"></a>
                    </div>
                    <input type="submit" value="Регистрация" class="modal-content__input modal-content__button" name="registration">
                </form>
            </div>
        </div>
    </div>
</div>
<div class="wrap">
    <header class="header">
        <a id="back2Top" title="Наверх" href="#">&#10148;</a>
        <div class="header__top">
            <div class="header__wrap">
                <div class="currency li-class">
                    <ul class="currency__select" data-selected-value="dollar">
                        <li class="currency__option"><span>dollar</span>
                            <ul class="currency__options">
                                <li data-value="dollar">dollar</li>
                                <li data-value="euro">euro</li>
                                <li data-value="ruble">ruble</li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="language li-class">
                    <ul class="language__select" data-selected-value="dollar">
                        <li class="language__option"><span>russian</span>
                            <ul class="language__options">
                                <li data-value="russian">russian</li>
                                <li data-value="english">english</li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="header__phone">
                <a class="icon-phone"> 8(800)-555-35-35</a>
            </div>
        </div>
        <div class="header__content">
            <div class="header__title"><a href="?main"><span>Football</span> Life</a></div>
            <div class="header__search">
                <form action="#" method="post">
                    <input type="text" placeholder="Я ищу..." name="search-text">
                    <label for="search"><picture><source srcset="../images/header/search.svg" type="image/webp"><img src="../images/header/search.svg" alt=""></picture></label>
                    <input type="submit" hidden id="search" name="search">
                </form>
            </div>
            <div class="header__icon">
                    <?php if(isset($_COOKIE['user']) || isset($_COOKIE['admin'])): ?>
                    <div class="header__icon-item">
                        <a class="icon-user" href="?user-profile"><br></a>
                        <a href="?user-profile" style="font-size: 20px;margin: 3px 0 0 0;">Профиль</a>
                    <?php else: ?>
                    <div class="header__icon-item reg-auth">
                        <i class="icon-user"><br></i>
                        <p>Войти</p>
                    <?php endif; ?>
                </div>
                <div class="header__icon-item">
                    <a class="icon-heart" href="?favorites"><br></a>
                    <p><a href="?favorites">Избранное</a></p>
                </div>
                <div class="header__icon-item">
                    <div class="header__icon-item">
                        <a class="icon-cart" href="?cart"><br></a>
                        <p><a href="?cart" onclick="sumPrice()">Корзина</a></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="header__bottom">
            <nav class="header__menu">
                <?php for($i = 0; $i < count($this->arrMenu); $i++): ?>
                    <div class="header__link"><a href="?id_category=<?=$this->arrMenu[$i]['id_category'] ?>"> <?= $this->arrMenu[$i]['name'] ?> </a></div>
                <?php endfor ;?>
            </nav>
        </div>
    </header>