<?php

class Core
{
    public $arr = [];
    public $arrCurrent = [];
    public $arrPictures = [];
    public $arrSpecProperties = [];
    public $arrSizes = [];
    public $arrSizesUnic = [];
    public $arrBrands = [];
    public $arrColors = [];
    public $arrReviews = [];
    public $arrMenu = [];
    public $arrCurrentCategories = [];
    public $minPrice;
    public $maxPrice;
    public $amountAllProducts = 0;
    public $amountPages = 0;
    public $currentPage = 0;
    public $title = '';
    public $arrInfoAboutUser = [];
    public $arrOrdersUser = [];
    public $arrProductsAtCart = [];
    public $arrReport = [];

    public function __construct()
    {
        $this->m = new Model();
    }

    protected function setContent() {
        $this->arr = $this->m->getContent();
    }

    protected function setVars() {
        $this->arrPictures = $this->m->getArrPictures();
        $this->arrSpecProperties = $this->m->getArrSpecProperties();
        $this->arrBrands = $this->m->getArrBrands();
        $this->arrColors = $this->m->getArrColors();
        $this->minPrice = $this->m->getMinPrice();
        $this->maxPrice = $this->m->getMaxPrice();
        $this->arrReviews = $this->m->getArrReviews();
        $this->arrMenu = $this->m->getArrMenu();
        $this->arrCurrentCategories = $this->m->getArrCurrentCategories();
        $this->amountAllProducts = $this->m->getAmountAllProducts($this->arrCurrentCategories);
        $this->amountPages = $this->m->getAmountPages($this->arr);
        $this->currentPage = $this->m->getCurrentPage($this->amountPages);
        $this->arrCurrent = $this->m->getArrCurrent($this->arr, $this->currentPage);
        $this->arrSizes = $this->m->getArrSizes($this->arrCurrent);
        $this->arrSizesUnic = $this->m->getArrSizesUnic($this->arrSizes);
        $this->title = $this->m->getTitle($this->arr);
        $this->arrInfoAboutUser = $this->m->getArrInfoAboutUser();
        $this->arrOrdersUser = $this->m->getArrOrdersUser();
        $this->arrProductsAtCart = $this->m->getArrProductsAtCart();
        $this->arrReport = $this->m->getArrReport();
    }

    protected function authorization() {
        $this->m->authorization();
    }

    protected function out() {
        $this->m->out();
    }

    protected function changeProfile() {
        $this->m->changeProfile();
    }

    protected function delProduct() {
        $this->m->delProduct();
    }

    protected function incProduct() {
        $this->m->incProduct();
    }

    protected function dicProduct() {
        $this->m->dicProduct();
    }

    protected function addProductToCart() {
        $this->m->addProductToCart();
    }

    protected function bookingStart() {
        $this->arrProductsAtCart = $this->m->bookingStart($this->arrProductsAtCart);
    }

    protected function PHPExcel() {
        $this->m->PHPExcel($this->arrReport);
    }

    public function setBody()
    {
        $this->setContent();
        $this->setVars();
        $this->authorization();
        $this->out();
        $this->changeProfile();
        $this->delProduct();
        $this->incProduct();
        $this->dicProduct();
        $this->addProductToCart();
        $this->bookingStart();
        $this->PHPExcel();

        require_once "view/View.php";
    }
}