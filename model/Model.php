<?php

class Model
{
    public function __construct()
    {
        $this->db = mysqli_connect(HOST,USER,PASSWORD,DB);
        if(!$this->db)
            echo "Ошибка соединения с БД";
    }

    public function getContent() {
        if(isset($_GET['cart']))
            return $this->getCart();
        elseif(isset($_GET['favorites']))
            return $this->getFavorities();
        elseif(isset($_GET['product']))
            return $this->getProduct();
        elseif(isset($_GET['user-profile']))
            return $this->getUserProfile();
        else
            return $this->getMain();
    }

    protected function getFavorities() {

    }

    protected function getCart() {

    }

    protected function getMain() {
        $filter = [];
        $strForFilter = '';

        if(isset($_POST['new']))
            $str = 'ORDER BY novelty DESC';
        elseif(isset($_POST['price-top']))
            $str = 'ORDER BY current_price ASC';
        elseif(isset($_POST['price-down']))
            $str = 'ORDER BY current_price DESC';
        elseif(isset($_POST['discount-top']))
            $str = 'ORDER BY round((p.old_price-p.current_price)/(p.old_price/100)) ASC';
        elseif(isset($_POST['discount-bottom']))
            $str = 'ORDER BY round((p.old_price-p.current_price)/(p.old_price/100)) DESC';
        else
            $str = 'ORDER BY (SELECT COUNT(rating) FROM review rev WHERE rev.id_product=p.id_product) DESC';

        if(isset($_POST['filter']) || isset($_POST['new']) || isset($_POST['popular']) || isset($_POST['price-top']) || isset($_POST['price-down']) || isset($_POST['discount-top']) || isset($_POST['discount-down'])) {
            for($i = 0; $i < 100; $i++) {
                if(isset($_POST["brand-".$i]))
                    $filter['brands'][] = $_POST["brand-".$i];
                if(isset($_POST["sidebar-check__size-".$i]))
                    $filter['sizes'][] = $_POST["sidebar-check__size-".$i];
                if(isset($_POST["color-".$i]))
                    $filter['colors'][] = $_POST["color-".$i];
                if(isset($_POST['min-price']))
                    $filter['min-price'][0] = $_POST['min-price'];
                if(isset($_POST['max-price']))
                    $filter['max-price'][1] = $_POST['max-price'];
            }

            if(!empty($filter['brands'])) {
                for($i = 0; $i < count($filter['brands']); $i++) {
                    $filter['brands'][$i] = trim($filter['brands'][$i]);

                    if($i === 0)
                        $strForFilter .= "WHERE (b.name='{$filter['brands'][$i]}'";
                    elseif($i !== 0)
                        $strForFilter .= " OR b.name='{$filter['brands'][$i]}'";
                    else
                        $strForFilter .= "AND (b.name='{$filter['brands'][$i]}'";

                    if(empty($filter['brands'][$i+1]))
                        $strForFilter .= ')';
                }
            }

            if(!empty($filter['sizes'])) {
                for($i = 0; $i < count($filter['sizes']); $i++) {
                    if($strForFilter === '')
                        $strForFilter .= "WHERE (value_property='{$filter['sizes'][$i]}'";
                    elseif($i !== 0)
                        $strForFilter .= " OR value_property='{$filter['sizes'][$i]}'";
                    else
                        $strForFilter .= "AND (value_property='{$filter['sizes'][$i]}'";

                    if(empty($filter['sizes'][$i+1]))
                        $strForFilter .= ')';
                }
            }

            if(!empty($filter['colors'])) {
                for($i = 0; $i < count($filter['colors']); $i++) {
                    if($strForFilter === '')
                        $strForFilter .= "WHERE (col.name='{$filter['colors'][$i]}'";
                    elseif($i !== 0)
                        $strForFilter .= " OR col.name='{$filter['colors'][$i]}'";
                    else
                        $strForFilter .= "AND (col.name='{$filter['colors'][$i]}'";

                    if(empty($filter['colors'][$i+1]))
                        $strForFilter .= ')';
                }
            }

            if(!empty($filter['min-price']) && !empty($filter['max-price'])) {
                if($strForFilter === '')
                    $strForFilter .= 'WHERE current_price>=' . $filter['min-price'][0] . ' AND current_price<=' . $filter['max-price'][1];
                else
                    $strForFilter .= ' AND current_price>=' . $filter['min-price'][0] . ' AND current_price<=' . $filter['max-price'][1];
            }
        }

        if(isset($_GET['id_category'])) {
            if($strForFilter === '')
                $strForFilter .= "WHERE (c.id_category='{$_GET['id_category']}')";
            else
                $strForFilter .= " AND (c.id_category='{$_GET['id_category']}')";
        }

        if(isset($_GET['id_subcategory'])) {
            if($strForFilter === '')
                $strForFilter .= "WHERE (sub.id_subcategory='{$_GET['id_subcategory']}')";
            else
                $strForFilter .= " AND (sub.id_subcategory='{$_GET['id_subcategory']}')";
        }

        if(isset($_POST['search']) && isset($_POST['search-text']) ) {
            if($strForFilter === '')
                $strForFilter .= "WHERE c.name LIKE '{$_POST['search-text']}' OR sub.name='{$_POST['search-text']}'";
            else
                $strForFilter .= " AND c.name LIKE '{$_POST['search-text']}' OR sub.name='{$_POST['search-text']}'";
        }

        $query = "SELECT p.id_product AS id, p.id_subcategory AS id_subcategory, c.id_category AS id_category, c.name AS name_ccategory, p.id_brand AS id_brand,p.name AS name, p.current_price AS current_price, p.old_price AS old_price, p.novelty AS novelty, (SELECT url FROM `picture` pi WHERE pi.id_product=p.id_product LIMIT 1) AS url, b.name AS name_brand, sub.name AS name_category,(SELECT AVG(r.rating) FROM `review` r WHERE r.id_product=p.id_product) AS rating, round((p.old_price-p.current_price)/(p.old_price/100)) AS discount, col.name AS color, p.id_product AS id_product
                    FROM `product` p LEFT JOIN brand b 
                    ON p.id_brand=b.id_brand
                    LEFT JOIN subcategory sub 
                    ON p.id_subcategory=sub.id_subcategory
                    LEFT JOIN category c 
                    ON c.id_category=sub.id_category 
                    LEFT JOIN color col 
                    ON p.id_color=col.id_color
                    LEFT JOIN additional_product_characteristic addit ON p.id_product=addit.id_product 
                    LEFT JOIN specific_property spec ON addit.id_property=spec.id_property";

        if($strForFilter !== '') {
            $query .= ' ' . $strForFilter;
        }

        $query .= ' ' . $str;

        $arr = $this->mysqlResultToArr($query);

        return $arr;
    }

    protected function getUserProfile() {

    }

    public function getAmountPages($arr) {
        if(empty($arr))
            return 0;

        $result = 0;

        for($i = 0; $i < count($arr); $i++) {
            if(($i+1) % 6 === 0)
                $result++;
            elseif(empty($arr[$i+1]))
                $result++;
        }

        return $result;
    }

    public function getCurrentPage($amountPages) {
        if(isset($_GET['page']))
            return intval($_GET['page']);

        return 1;
    }

    public function getArrCurrentCategories() {
        if(isset($_POST['search']))
            $query = "SELECT sub.id_category AS id,id_subcategory AS id_subcategory, sub.name AS name, (SELECT COUNT(id_product) FROM product p WHERE p.id_subcategory=sub.id_subcategory) AS amount FROM `subcategory` sub 
                        LEFT JOIN category c
                        ON sub.id_category=c.id_category
                        WHERE c.name='{$_POST['search-text']}' OR sub.name='{$_POST['search-text']}'";
        elseif(isset($_GET['id_category']))
            $query = "SELECT sub.id_category AS id,id_subcategory AS id_subcategory, sub.name, (SELECT COUNT(id_product) FROM product p WHERE p.id_subcategory=sub.id_subcategory) AS amount FROM `subcategory` sub 
                        LEFT JOIN category c
                        ON sub.id_category=c.id_category
                        WHERE sub.id_category='{$_GET['id_category']}'";
        elseif(isset($_GET['id_subcategory']))
            $query = "SELECT sub.id_category AS id,id_subcategory AS id_subcategory, sub.name, (SELECT COUNT(id_product) FROM product p WHERE p.id_subcategory=sub.id_subcategory) AS amount FROM `subcategory` sub 
                        LEFT JOIN category c
                        ON sub.id_category=c.id_category
                        WHERE sub.id_subcategory='{$_GET['id_subcategory']}'";
        else
            $query = "SELECT id_subcategory AS id_subcategory, name, (SELECT COUNT(id_product) FROM product p WHERE p.id_subcategory=sub.id_subcategory) AS amount FROM `subcategory` sub";

        $arr = $this->mysqlResultToArr($query);

        return $arr;
    }

    public function getArrCurrent($arr, $currentPage) {
        $result = [];
        $count = 0;

        if(empty($arr))
            return $result;

        for($i = 0; $i < count($arr);) {
            if($count + 1 == $currentPage) {
               for($j = $i; $j < $i + 6; $j++){
                   $result[] = $arr[$j];

                   if(empty($arr[$j+1]))
                       break;
               }
            }

            $i += 6;
            $count++;
        }

        return $result;
    }

    public function getAmountAllProducts($arr){
        $result = 0;

        if(empty($arr[0]))
            return  $result;

        for($i =0; $i < count($arr); $i++)
            $result += intval($arr[$i]['amount']);

        return $result;
    }

    public function getArrMenu() {
        $query = "SELECT * FROM `category`";
        $arr = $this->mysqlResultToArr($query);

        return $arr;
    }

    protected function getProduct() {
        $query = "SELECT p.id_product AS id, p.id_subcategory AS id_subcategory, p.id_brand AS id_brand,p.name AS name, p.amount AS amount, p.current_price AS current_price, p.old_price AS old_price, p.novelty AS novelty, pi.url AS url, b.name AS name_brand, sub.name AS name_category, r.rating AS rating, co.name AS name_country, col.name AS name_color, round((p.old_price-p.current_price)/(p.old_price/100)) AS discount
                    FROM `product` p LEFT JOIN `picture` pi 
                    ON p.id_product=pi.id_product
                    LEFT JOIN brand b 
                    ON p.id_brand=b.id_brand
                    LEFT JOIN subcategory sub 
                    ON p.id_subcategory=sub.id_subcategory
                    LEFT JOIN review r 
                    ON p.id_product=r.id_product
                    LEFT JOIN country co 
                    ON p.id_country=co.id_country
                    LEFT JOIN color col 
                    ON p.id_color=col.id_color
                    WHERE p.id_product={$_GET['product']}";
        $arr = $this->mysqlResultToArr($query);

        return $arr;
    }

    public function getArrPictures() {
        if(!isset($_GET['product']))
            return [];

        $query = "SELECT url FROM `picture` WHERE id_product={$_GET['product']}";
        $arr = $this->mysqlResultToArr($query);

        return $arr;
    }

    public function getArrBrands() {
        if(isset($_POST['search']))
            $query = "SELECT b.name FROM `brand` b
                        LEFT JOIN `product` p ON p.id_brand=b.id_brand 
                        LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory 
                        LEFT JOIN `category` c ON c.id_category=sub.id_category
                        WHERE sub.name='{$_POST['search-text']}' OR c.name='{$_POST['search-text']}'
                        GROUP by b.name";
        elseif(isset($_GET['id_category']))
            $query = "SELECT b.name FROM `brand` b
                        LEFT JOIN `product` p ON p.id_brand=b.id_brand 
                        LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory 
                        LEFT JOIN `category` c ON c.id_category=sub.id_category
                        WHERE c.id_category='{$_GET['id_category']}'
                        GROUP by b.name";
        elseif(isset($_GET['id_subcategory']))
            $query = "SELECT b.name FROM `brand` b
                        LEFT JOIN `product` p ON p.id_brand=b.id_brand 
                        LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory 
                        LEFT JOIN `category` c ON c.id_category=sub.id_category
                        WHERE sub.id_subcategory='{$_GET['id_subcategory']}'
                        GROUP by b.name";
        else
            $query = "SELECT name FROM `brand`";

        $arr = $this->mysqlResultToArr($query);

        return $arr;
    }

    public function getTitle($arr) {
        if(empty($arr[0]) || isset($_GET['product']))
            return '';

        $id = $arr[0]['id_category'];
        $result = '';

        for($i = 1; $i < count($arr); $i++) {
            if($arr[$i]['id_category'] !== $id)
                $result = 'Все товары';
        }

        if($result === '')
            $result = $arr[0]['name_ccategory'];

        return $result;
    }

    public function getArrSizesUnic($arr) {
        $result = [];
        $fl = true;

        if(empty($arr[0]))
            return [];

        for($i = 0; $i < count($arr); $i++) {
            for($j = 0; $j < count($result); $j++){
                if($result[$j] === $arr[$i]['value'])
                    $fl = false;
            }

            if($fl)
                $result[] = $arr[$i]['value'];

            $fl = true;
        }

        return $result;
    }

    public function getArrSpecProperties() {
        if(!isset($_GET['product']))
            return [];

        $query = "SELECT id_product, a.id_property, value_property AS value, s.name AS name
                    FROM `additional_product_characteristic` a 
                    LEFT JOIN `specific_property` s
                    ON a.id_property=s.id_property
                    WHERE name='Материал'";
        $arr = $this->mysqlResultToArr($query);

        return $arr;
    }

    public function getArrColors() {
        if(isset($_POST['search']))
            $query = "SELECT col.name AS name FROM `color` col
                    LEFT JOIN `product` p ON col.id_color=p.id_color
                    LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory
                    LEFT JOIN `category` c ON sub.id_category=c.id_category
                    WHERE sub.name='{$_POST['search-text']}' OR c.name='{$_POST['search-text']}'
                    GROUP BY col.name";
        elseif(isset($_GET['id_category']))
            $query = "SELECT col.name AS name FROM `color` col
                        LEFT JOIN `product` p ON col.id_color=p.id_color
                        LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory
                        LEFT JOIN `category` cat ON sub.id_category=cat.id_category
                        WHERE cat.id_category='{$_GET['id_category']}'
                        GROUP BY name";
        elseif(isset($_GET['id_subcategory']))
            $query = "SELECT col.name AS name FROM `color` col
                        LEFT JOIN `product` p ON col.id_color=p.id_color
                        LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory
                        LEFT JOIN `category` cat ON sub.id_category=cat.id_category
                        WHERE sub.id_subcategory='{$_GET['id_subcategory']}'
                        GROUP BY name";
        else
        $query = "SELECT col.name AS name FROM `color` col
                    LEFT JOIN `product` p ON col.id_color=p.id_color
                    LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory
                    LEFT JOIN `category` cat ON sub.id_category=cat.id_category
                    GROUP BY name";

        $arr = $this->mysqlResultToArr($query);

        return $arr;
    }

    public function getArrSizes($arr = []) {
        if(isset($_POST['search']))
            $query = "SELECT a.id_product, a.id_property, value_property AS value, s.name AS name
                    FROM `additional_product_characteristic` a 
                    LEFT JOIN `specific_property` s
                    ON a.id_property=s.id_property
                    LEFT JOIN `product` p
                    ON a.id_product=p.id_product
                    LEFT JOIN `subcategory` sub 
                    ON p.id_subcategory=sub.id_subcategory
                    LEFT JOIN `category` c 
                    ON sub.id_category=c.id_category
                    WHERE sub.name=('{$_POST['search-text']}' OR c.name='{$_POST['search-text']}') AND a.value_property > 0
                    ORDER BY value ASC";
        elseif(isset($_GET['id_category']))
            $query = "SELECT a.id_product, a.id_property, value_property AS value, s.name AS name
            FROM `additional_product_characteristic` a 
            LEFT JOIN `specific_property` s
            ON a.id_property=s.id_property
            LEFT JOIN `product` p
            ON a.id_product=p.id_product
            LEFT JOIN `subcategory` sub 
            ON p.id_subcategory=sub.id_subcategory
            LEFT JOIN `category` c 
            ON sub.id_category=c.id_category
            WHERE s.name='Размер' AND c.id_category='{$_GET['id_category']}'  AND a.value_property > 0
            ORDER BY value ASC";
        elseif(isset($_GET['id_subcategory']))
            $query = "SELECT a.id_product, a.id_property, value_property AS value, s.name AS name
                        FROM `additional_product_characteristic` a 
                        LEFT JOIN `specific_property` s
                        ON a.id_property=s.id_property
                        LEFT JOIN `product` p
                        ON a.id_product=p.id_product
                        LEFT JOIN `subcategory` sub 
                        ON p.id_subcategory=sub.id_subcategory
                        LEFT JOIN `category` c 
                        ON sub.id_category=c.id_category
                        WHERE sub.id_subcategory='{$_GET['id_subcategory']}'  AND a.value_property > 0
                        ORDER BY value ASC";
        elseif(isset($_GET['page'])) {
            for($i = 0; $i < count($arr); $i++) {
                if($i === 0)
                    $str = " WHERE a.id_product='{$arr[$i]['id']}' ";
                elseif(empty($arr[$i+1]['id']))
                    $str .= " OR a.id_product='{$arr[$i]['id']}') ";
                else
                    $str .= " OR a.id_product='{$arr[$i]['id']}' ";
            }
            $query = "SELECT a.id_product AS id_product, a.id_property, value_property AS value, s.name AS name
                        FROM `additional_product_characteristic` a
                        LEFT JOIN `specific_property` s
                        ON a.id_property=s.id_property
                        LEFT JOIN `product` p
                        ON a.id_product=p.id_product
                        LEFT JOIN `subcategory` sub
                        ON p.id_subcategory=sub.id_subcategory
                        LEFT JOIN `category` c
                        ON sub.id_category=c.id_category
                        $str AND a.value_property > 0
                        ORDER BY value ASC";
        }
        else
            $query = "SELECT id_product, a.id_property, value_property AS value, s.name AS name
                    FROM `additional_product_characteristic` a 
                    LEFT JOIN `specific_property` s
                    ON a.id_property=s.id_property
                    WHERE name='Размер' AND a.value_property > 0
                    ORDER BY value ASC";

        $arr = $this->mysqlResultToArr($query);

        return $arr;
    }

    public function getMinPrice() {
        if(isset($_POST['search']))
            $query = "SELECT MIN(current_price) AS min_price FROM `product` p
                        LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory
                        LEFT JOIN `category` c ON sub.id_category=c.id_category
                        WHERE sub.name='{$_POST['search-text']}' OR c.name='{$_POST['search-text']}'
                        LIMIT 1";
        elseif(isset($_GET['id_category']))
            $query = "SELECT MIN(current_price) AS min_price FROM `product` p
                        LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory
                        LEFT JOIN `category` c ON sub.id_category=c.id_category
                        WHERE c.id_category='{$_GET['id_category']}'
                        LIMIT 1";
        elseif(isset($_GET['id_subcategory']))
            $query = "SELECT MIN(current_price) AS min_price FROM `product` p
                        LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory
                        LEFT JOIN `category` c ON sub.id_category=c.id_category
                        WHERE sub.id_subcategory='{$_GET['id_subcategory']}'
                        LIMIT 1";
        else
            $query = "SELECT MIN(current_price) AS min_price FROM `product` p
                        LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory
                        LEFT JOIN `category` c ON sub.id_category=c.id_category
                        LIMIT 1";

        $result = $this->mysqlResultToArr($query)[0]['min_price'];

        return $result;
    }

    public function getMaxPrice() {
        if(isset($_POST['search']))
            $query = "SELECT MAX(current_price) AS min_price FROM `product` p
                        LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory
                        LEFT JOIN `category` c ON sub.id_category=c.id_category
                        WHERE sub.name='{$_POST['search-text']}' OR c.name='{$_POST['search-text']}'
                        LIMIT 1";
        elseif(isset($_GET['id_category']))
            $query = "SELECT MAX(current_price) AS min_price FROM `product` p
                        LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory
                        LEFT JOIN `category` c ON sub.id_category=c.id_category
                        WHERE c.id_category='{$_GET['id_category']}'
                        LIMIT 1";
        elseif(isset($_GET['id_subcategory']))
            $query = "SELECT MAX(current_price) AS min_price FROM `product` p
                        LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory
                        LEFT JOIN `category` c ON sub.id_category=c.id_category
                        WHERE sub.id_subcategory='{$_GET['id_subcategory']}'
                        LIMIT 1";
        else
            $query = "SELECT MAX(current_price) AS min_price FROM `product` p
                        LEFT JOIN `subcategory` sub ON p.id_subcategory=sub.id_subcategory
                        LEFT JOIN `category` c ON sub.id_category=c.id_category
                        LIMIT 1";

        $result = $this->mysqlResultToArr($query)[0]['min_price'];

        return $result;
    }

    public function getArrReviews() {
        $query = 'SELECT r.id_product AS id_product, r.id_client AS id_client, c.name AS name, r.text AS text, c.url AS url, r.date AS date, r.rating AS rating
                    FROM `review` r
                    LEFT JOIN `client` c
                    ON r.id_client=c.id_client
                    WHERE r.text>""';
        $result = $this->mysqlResultToArr($query);

        return $result;
    }

    public function dump($arr){
        echo '<pre>' . print_r($arr, true) . '</pre>';
    }

    protected function mysqlResultToArr($query = '', $fl = false) {
        if($query === '')
            return [];

        $result = mysqli_query($this->db, $query);
        $count = 0;

        if($result == false || $fl || $result === true)
            return [];

        $arr[] = mysqli_fetch_assoc($result);

        while($row = mysqli_fetch_assoc($result)) {
            foreach ($arr as $item) {
                if($item != $row)
                    $count +=1;
            }

            if($count === count($arr))
                $arr[] = $row;

            $count = 0;
        }

        return $arr;
    }

    public function authorization() {
        if(!isset($_POST['authorization']))
            return;

        $query = "SELECT c.id_client AS id_client, r.name AS role FROM `client` c
                    LEFT JOIN `role` r ON c.id_role=r.id_role
                    WHERE (login='{$_POST['authorization-emailorphone']}' OR telephone='{$_POST['authorization-emailorphone']}') AND password='{$_POST['authorization-password']}'";
        $result = $this->mysqlResultToArr($query);

        if(empty($result[0]))
            $_SESSION['authorization'] = 'Неверно введен логин или пароль!';
        else {
            if($result[0]['role'] === 'Пользователь'){
                if(!empty($_POST['authorization-memory'])){
                    setcookie("user", $result[0]['id_client'], time()+1000000);
                    setcookie("remember", $result[0]['id_client'], time()+1000000);
                }
                else
                    setcookie("user", $result[0]['id_client'], time()+3600);
            }
            else
                if(!empty($_POST['authorization-memory'])) {
                    setcookie("admin", $result[0]['id_client'], time()+1000000);
                    setcookie("remember", $result[0]['id_client'], time()+1000000);
                }
                else
                    setcookie("admin", $result[0]['id_client'], time()+3600);
        }

        header('Location: http://online-store/');
        die();
    }

    public function out() {
        if(!isset($_GET['out']))
            return;

        if(isset($_COOKIE['user'])){
            if(isset($_COOKIE['remember'])) {
                setcookie("user", $_COOKIE['user'], time()-1000000);
                setcookie("remember", $_COOKIE['user'], time()-1000000);
            } else
                setcookie("user", $_COOKIE['user'], time()-3600);
        } else {
            if(isset($_COOKIE['remember'])) {
                setcookie("admin", $_COOKIE['admin'], time()-1000000);
                setcookie("remember", $_COOKIE['admin'], time()-1000000);
            } else
                setcookie("admin", $_COOKIE['admin'], time()-3600);
        }

        header('Location: http://online-store/');
        die();
    }

    public function getArrInfoAboutUser() {
        if(!isset($_COOKIE['user']) && !isset($_COOKIE['admin']))
            return [];

        if(isset($_COOKIE['user']))
            $query = "SELECT * FROM `client` WHERE id_client='{$_COOKIE['user']}'";
        else
            $query = "SELECT * FROM `client` WHERE id_client='{$_COOKIE['admin']}'";

        $result = $this->mysqlResultToArr($query);

        return $result;
    }

    public function changeProfile() {
        if(!isset($_POST['change-profile']) && !isset($_POST['change-password']))
            return;

        if(isset($_POST['change-profile'])) {
            if(isset($_COOKIE['user'])) {
                if(!empty($_POST['change-url']))
                    $query = "UPDATE `client` SET `login`='{$_POST['change-login']}',`lastname`='{$_POST['change-lastname']}',`name`='{$_POST['change-name']}',`url`='../images/content/avatars/{$_POST['change-url']}',
                         address='{$_POST['change-address']}',`patronymic`='{$_POST['change-patronymic']}',`telephone`='{$_POST['change-telephone']}'
                        WHERE id_client='{$_COOKIE['user']}'";
                else
                    $query = "UPDATE `client` SET `login`='{$_POST['change-login']}',`lastname`='{$_POST['change-lastname']}',`name`='{$_POST['change-name']}',
                         address='{$_POST['change-address']}',`patronymic`='{$_POST['change-patronymic']}',`telephone`='{$_POST['change-telephone']}'
                        WHERE id_client='{$_COOKIE['user']}'";
            }
            else {
                if(!empty($_POST['change-url']))
                    $query = "UPDATE `client` SET `login`='{$_POST['change-login']}',`lastname`='{$_POST['change-lastname']}',`name`='{$_POST['change-name']}',`url`='../images/content/avatars/{$_POST['change-url']}',
                         address='{$_POST['change-address']}',`patronymic`='{$_POST['change-patronymic']}',`telephone`='{$_POST['change-telephone']}'
                        WHERE id_client='{$_COOKIE['admin']}'";
                else
                    $query = "UPDATE `client` SET `login`='{$_POST['change-login']}',`lastname`='{$_POST['change-lastname']}',`name`='{$_POST['change-name']}',
                         address='{$_POST['change-address']}',`patronymic`='{$_POST['change-patronymic']}',`telephone`='{$_POST['change-telephone']}'
                        WHERE id_client='{$_COOKIE['admin']}'";
            }
        }
        else{
            if($_POST['change-password1'] === $_POST['change-password2']) {
                if(isset($_COOKIE['user'])) {
                    $query = "UPDATE `client` SET `password`='{$_POST['change-password2']}'
                    WHERE id_client='{$_COOKIE['user']}'";
                }
                else {
                    $query = "UPDATE `client` SET `password`='{$_POST['change-password2']}'
                    WHERE id_client='{$_COOKIE['admin']}'";
                }
                $_SESSION['change-password'] = 'Пароль успешно изменен!';
            } else {
                $_SESSION['change-password'] = 'Пароли не совпадают!';
                return;
            }
        }

        $this->mysqlResultToArr($query);

        if(isset($_SESSION['change-password']))
            return;

        header('Location: http://online-store/?user-profile');
        die();
    }

    public function getArrOrdersUser() {
        if(!isset($_COOKIE['user']))
            return [];

        $query = "SELECT p.id_product AS id_product, p.name AS name, p.current_price AS current_price, MIN(pic.url) AS url
                    FROM `booking` b
                    LEFT JOIN product p
                    ON b.id_product=p.id_product
                    LEFT JOIN picture pic
                    ON b.id_product=pic.id_product
                    LEFT JOIN booking_status bs
                    ON b.id_booking_status=bs.id_booking_status
                    WHERE b.id_client='{$_COOKIE['user']}' AND bs.name='Доставлен'
                    GROUP BY p.id_product";

        return $this->mysqlResultToArr($query);
    }
    
    public function delProduct() {
        for($i = 0; $i < 100; $i++) {
            if(isset($_POST['del-product-'.$i])){
                break;
            }
            if($i + 1 === 100)
                return;
        }

        $query = "DELETE FROM `booking` WHERE id_booking='{$_POST['id_booking']}'";
        $this->mysqlResultToArr($query);

        header('Location: http://online-store/?cart');
        die();
    }

    public function incProduct() {
        for($i = 0; $i < 100; $i++) {
            if(isset($_POST['inc-product-'.$i])){
                $amount = intval($_POST['inc-product-'.$i]);
                break;
            }
            if($i + 1 === 100)
                return;
        }

        $amount++;

        $query = "UPDATE `booking` SET `amount`='{$amount}' WHERE id_booking={$_POST['id_booking']}";
        $this->mysqlResultToArr($query);

        header('Location: http://online-store/?cart');
        die();
    }

    public function addProductToCart() {
        for($i = 0; $i < 100; $i++) {
            if(isset($_POST['add-product-to-cart-'.$i]))
                break;
            if($i + 1 === 100)
                return;
        }

        $query = "INSERT INTO `booking`(`id_product`, `id_client`, `id_delivery_method`, `id_payment_method`, `size`, `date`, `id_booking_status`, `amount`, `comment`, `delivery_period`, `price_delivery`) VALUES ('{$_POST['id_productt']}','{$_COOKIE['user']}', '1', '1', '{$_POST['size']}','2021-06-17 17:25:16','1','1', '', '', 0)";
        $this->mysqlResultToArr($query);

        if(isset($_POST['www']))
            header("Location: http://online-store/?product={$_POST['id_productt']}");
        else
            header('Location: http://online-store/?main');
        die();
    }

    public function bookingStart($arr) {
        if(!isset($_POST['booking-start']))
            return $arr;

        $date = date('Y-m-d H:i:s');

        for($i = 0; $i < count($arr); $i++) {
            if($_POST['radio-delivery'] == 2)
                $query = "UPDATE `booking` SET `id_delivery_method`='{$_POST['radio-delivery']}',`id_payment_method`='{$_POST['radio-pay']}',`date`='{$date}',
                        `id_booking_status`=2,`comment`='{$_POST['delivery-comment']}',`delivery_period`='{$_POST['delivery-days-1']}', `price_delivery`='{$_POST['delivery-price-1']}'  WHERE id_product='{$arr[$i]['id_product']}' AND id_client='{$_COOKIE['user']}'";
            elseif($_POST['radio-delivery'] == 3)
                $query = "UPDATE `booking` SET `id_delivery_method`='{$_POST['radio-delivery']}',`id_payment_method`='{$_POST['radio-pay']}',`date`='{$date}',
                        `id_booking_status`=2,`comment`='{$_POST['delivery-comment']}',`delivery_period`='{$_POST['delivery-days-2']}', `price_delivery`='{$_POST['delivery-price-2']}' WHERE id_product='{$arr[$i]['id_product']}' AND id_client='{$_COOKIE['user']}'";
            else
                $query = "UPDATE `booking` SET `id_delivery_method`='{$_POST['radio-delivery']}',`id_payment_method`='{$_POST['radio-pay']}',`date`='{$date}',
                        `id_booking_status`=2,`comment`='{$_POST['delivery-comment']}',`delivery_period`='{$_POST['delivery-days-3']}', `price_delivery`='{$_POST['delivery-price-3']}' WHERE id_product='{$arr[$i]['id_product']}' AND id_client='{$_COOKIE['user']}'";

            $this->mysqlResultToArr($query);

            if(empty($arr[$i+1])) {
                header('Location: http://online-store/?cart');
                die();
            }
        }

        return [];
    }

    public function dicProduct() {
        for($i = 0; $i < 100; $i++) {
            if(isset($_POST['dic-product-'.$i])){
                $amount = intval($_POST['dic-product-'.$i]);
                break;
            }
            if($i + 1 === 100)
                return;
        }

        $amount--;

        if($amount === 0)
            $query = "DELETE FROM `booking` WHERE id_booking={$_POST['id_booking']}";
        else
            $query = "UPDATE `booking` SET `amount`='{$amount}' WHERE id_booking={$_POST['id_booking']}";

        $this->mysqlResultToArr($query);

        header('Location: http://online-store/?cart');
        die();
    }

    public function getArrProductsAtCart() {
        if(!isset($_COOKIE['user']))
            return [];

        $query = "SELECT MIN(p.id_product) AS id_product, MIN(id_booking) AS id_booking, MIN(p.name) AS name, MIN(p.current_price) AS current_price, MIN(p.old_price) AS old_price,  MIN(pic.url) AS url, MAX(b.amount) AS amount, b.size AS size
                    FROM `booking` b
                    LEFT JOIN product p
                    ON b.id_product=p.id_product
                    LEFT JOIN picture pic
                    ON b.id_product=pic.id_product
                    LEFT JOIN booking_status bs
                    ON b.id_booking_status=bs.id_booking_status
                    LEFT JOIN additional_product_characteristic apc
                    ON apc.id_product=p.id_product
                    LEFT JOIN specific_property sp
                    ON apc.id_property=sp.id_property
                    WHERE b.id_client='{$_COOKIE['user']}' AND bs.name='Корзина'
                    GROUP BY b.size";

        return $this->mysqlResultToArr($query);
    }

    public function getArrReport() {
        if(!isset($_COOKIE['user']))
            return [];

        $query = "SELECT b.id_booking AS id_booking, p.name AS name_product, b.id_client AS id_client, dm.name AS name_delivery, pm.name AS name_payment, b.size AS size, b.date AS date, b.id_booking_status AS id_booking_status, b.amount AS amount, b.comment AS comment, b.delivery_period AS delivery_period, b.price_delivery AS price_delivery, p.current_price AS price, br.name AS name_brand, col.name AS name_color, coun.name AS name_country, cl.lastname AS lastname_client, cl.name AS name_client, cl.patronymic AS patronymic_client, cl.telephone AS telephone_client, cl.address AS address_client
                    FROM `booking` b
                    LEFT JOIN `product` p 
                    ON b.id_product=p.id_product
                    LEFT JOIN `brand` br 
                    ON br.id_brand=p.id_brand
                    LEFT JOIN `color` col 
                    ON col.id_color=p.id_color
                    LEFT JOIN `country` coun 
                    ON coun.id_country=p.id_country
                    LEFT JOIN `client` cl 
                    ON cl.id_client=b.id_client
                    LEFT JOIN `delivery_method` dm 
                    ON dm.id_delivery_method=b.id_delivery_method
                    LEFT JOIN `payment_method` pm 
                    ON pm.id_payment_method=b.id_payment_method
                    WHERE `id_booking_status`='2' AND b.id_client='{$_COOKIE['user']}'";

        return $this->mysqlResultToArr($query);
    }

    public function PHPExcel($arr) {
        if(!isset($_POST['report']))
            return false;

        $this->objPHPExcel = new PHPExcel();
        $this->objPHPExcel->setActiveSheetIndex(0);
        $nameFile = '';

        $this->activeSheet = $this->objPHPExcel->getActiveSheet();

        $this->activeSheet->getPageSetup()
            ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $this->activeSheet->getPageSetup()
            ->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        $this->downloadReportAboutBooking($arr);
        $nameFile = 'Отчет по текущем заказу пользователя';

        header("Content-Type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=$nameFile.xls");

        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    protected function downloadReportAboutBooking($arr) {
        $sumPrice = 0;
        $date = date('Y-m-d');
        $this->activeSheet->mergeCells('A1:G1');
        $this->activeSheet->getRowDimension('1')->setRowHeight(40);
        $this->activeSheet->getRowDimension('9')->setRowHeight(40);

        $this->activeSheet->setCellValue('A1','Информация о заказе');

        for($i = 0; $i < count($arr); $i++)
            $sumPrice += intval($arr[$i]['price']);

        $this->activeSheet->setCellValue('A2','Фамилия');
        $this->activeSheet->setCellValue('B2','Имя');
        $this->activeSheet->setCellValue('C2','Отчество');
        $this->activeSheet->setCellValue('D2','Комментарий');
        $this->activeSheet->setCellValue('E2','Срок доставки');
        $this->activeSheet->setCellValue('F2','Способ доставки');
        $this->activeSheet->setCellValue('G2','Способ оплаты');
        $this->activeSheet->setCellValue('A5','Адрес');
        $this->activeSheet->setCellValue('B5','Номер телефона');
        $this->activeSheet->setCellValue('C5','Общая сумма');
        $this->activeSheet->setCellValue('D5','Дата');

        $this->activeSheet->setCellValue('A3',"{$arr[0]['lastname_client']}");
        $this->activeSheet->setCellValue('B3',"{$arr[0]['name_client']}");
        $this->activeSheet->setCellValue('C3',"{$arr[0]['patronymic_client']}");
        $this->activeSheet->setCellValue('D3',"{$arr[0]['comment']}");
        $this->activeSheet->setCellValue('E3',"{$arr[0]['delivery_period']}");
        $this->activeSheet->setCellValue('F3',"{$arr[0]['name_delivery']}");
        $this->activeSheet->setCellValue('G3',"{$arr[0]['name_payment']}");
        $this->activeSheet->setCellValue('A6',"{$arr[0]['address_client']}");
        $this->activeSheet->setCellValue('B6',"{$arr[0]['telephone_client']}");
        $this->activeSheet->setCellValue('C6',"{$sumPrice}");
        $this->activeSheet->setCellValue('D6',"{$arr[0]['date']}");

        $this->activeSheet->setCellValue('A9','Информация о заказанных товарах');
        $this->activeSheet->mergeCells('A9:G9');

        $this->activeSheet->setCellValue('A10','Название');
        $this->activeSheet->setCellValue('B10','Цена');
        $this->activeSheet->setCellValue('C10','Размер');
        $this->activeSheet->setCellValue('D10','Страна производителя');
        $this->activeSheet->setCellValue('E10','Цвет');
        $this->activeSheet->setCellValue('F10','Бренд');
        $this->activeSheet->setCellValue('G10','Количество');

        $this->activeSheet->setTitle("Заказ");

        $styleHeader1 = array(
            'font'=>array(
                'bold' => true,
                'name' => 'Times New Roman',
                'size' => 20
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
            ),
            'fill' => array(
                'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
                'color'=>array(
                    'rgb' => 'E0ECF8'
                )
            ),
            'borders'=>array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                ),
            )
        );

        $styleHeader2 = array(
            'font'=>array(
                'bold' => true,
                'name' => 'Times New Roman',
                'size' => 20
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
            ),
            'fill' => array(
                'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
                'color'=>array(
                    'rgb' => 'FBEFFB'
                )
            ),
            'borders'=>array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                ),
            )
        );

        $styleTitle = array(
            'font'=>array(
                'name' => 'Times New Roman',
                'size' => 12
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
            ),
            'fill' => array(
                'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
                'color'=>array(
                    'rgb' => 'FBFBEF'
                )),
            'borders'=>array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                ),
            )
        );

        $styleContent = array(
            'font'=>array(
                'name' => 'Times New Roman',
                'size' => 12
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
            ),
            'fill' => array(
                'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
                'color'=>array(
                    'rgb' => 'F2F2F2'
                )),
            'borders'=>array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                ),
            )
        );

        for($i = 0; $i < count($arr); $i++) {
            $c = $i + 11;
            $this->activeSheet->setCellValue("A$c","{$arr[$i]['name_product']}");
            $this->activeSheet->setCellValue("B$c","{$arr[$i]['price']}");
            $this->activeSheet->setCellValue("C$c","{$arr[$i]['size']}");
            $this->activeSheet->setCellValue("D$c","{$arr[$i]['name_country']}");
            $this->activeSheet->setCellValue("E$c","{$arr[$i]['name_color']}");
            $this->activeSheet->setCellValue("F$c","{$arr[$i]['name_brand']}");
            $this->activeSheet->setCellValue("G$c","{$arr[$i]['amount']}");

            $this->activeSheet->getStyle("A$c")->applyFromArray($styleContent);
            $this->activeSheet->getStyle("B$c")->applyFromArray($styleContent);
            $this->activeSheet->getStyle("C$c")->applyFromArray($styleContent);
            $this->activeSheet->getStyle("D$c")->applyFromArray($styleContent);
            $this->activeSheet->getStyle("E$c")->applyFromArray($styleContent);
            $this->activeSheet->getStyle("F$c")->applyFromArray($styleContent);
            $this->activeSheet->getStyle("G$c")->applyFromArray($styleContent);
        }

        $this->activeSheet->getStyle('A2')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('B2')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('C2')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('D2')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('E2')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('F2')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('G2')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('A5')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('B5')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('C5')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('D5')->applyFromArray($styleTitle);

        $this->activeSheet->getStyle('A3')->applyFromArray($styleContent);
        $this->activeSheet->getStyle('B3')->applyFromArray($styleContent);
        $this->activeSheet->getStyle('C3')->applyFromArray($styleContent);
        $this->activeSheet->getStyle('D3')->applyFromArray($styleContent);
        $this->activeSheet->getStyle('E3')->applyFromArray($styleContent);
        $this->activeSheet->getStyle('F3')->applyFromArray($styleContent);
        $this->activeSheet->getStyle('G3')->applyFromArray($styleContent);
        $this->activeSheet->getStyle('A6')->applyFromArray($styleContent);
        $this->activeSheet->getStyle('B6')->applyFromArray($styleContent);
        $this->activeSheet->getStyle('C6')->applyFromArray($styleContent);
        $this->activeSheet->getStyle('D6')->applyFromArray($styleContent);

        $this->activeSheet->getStyle('A10')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('B10')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('C10')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('D10')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('E10')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('F10')->applyFromArray($styleTitle);
        $this->activeSheet->getStyle('G10')->applyFromArray($styleTitle);

        $this->activeSheet->getStyle('A1:G1')->applyFromArray($styleHeader1);
        $this->activeSheet->getStyle('A9:G9')->applyFromArray($styleHeader2);

        $this->activeSheet->getColumnDimension('A')->setWidth(25);
        $this->activeSheet->getColumnDimension('B')->setWidth(16);
        $this->activeSheet->getColumnDimension('C')->setWidth(15);
        $this->activeSheet->getColumnDimension('D')->setWidth(22);
        $this->activeSheet->getColumnDimension('E')->setWidth(17);
        $this->activeSheet->getColumnDimension('F')->setWidth(17);
        $this->activeSheet->getColumnDimension('G')->setWidth(15);
    }

}