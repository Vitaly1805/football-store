<?php

require "files/header.php";
if(isset($_GET['cart']))
     require "files/cart.php";
elseif(isset($_GET['favorites']))
    require "files/favorites.php";
elseif(isset($_GET['product']))
    require "files/product.php";
elseif(isset($_GET['user-profile']))
    require "files/user-profile.php";
else
    require "files/content.php";
require "files/footer.php";