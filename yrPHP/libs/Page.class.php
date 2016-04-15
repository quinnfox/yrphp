<?php
/**
 * Created by yrPHP.
 * User: Quinn
 * QQ: 284843370
 * Email: quinnH@163.com
 */
namespace libs;


class Page
{
    private $url = '';//当前链接URL
    private $urlParam = array();// 分页跳转时要带的参数
    private $totalPages; // 分页总页面数

    private $totalRows; // 总行数
    private $listRows = 12;// 列表每页显示行数

    private $rollPage = 8;// 分页栏每页显示的页数
    private $p = 'p'; //分页参数名
    private $gotoPage = false;//是否显示下来跳转

    private $nowTagOpen = "<strong>";
    private $nowPage = 1; //当前页
    private $nowTagClose = "</strong>";

    //整个分页周围围绕一些标签
    private $fullTagOpen = "";
    private $fullTagClose = "";

    private $firstTagOpen = "";
    private $firstLink = '首页';
    private $firstTagClose = "";

    private $lastTagOpen = "";
    private $lastLink = '尾页';
    private $lastTagClose = "";

    private $prevTagOpen = "";
    private $prevLink = '上一页';
    private $prevTagClose = "";

    private $nextTagOpen = "";
    private $nextLink = '下一页';
    private $nextTagClose = "";

    //其他页
    private $otherTagOpen = '';
    private $otherTagClose = '';


    public function __construct($config = array())
    {
        $this->init($config);
    }

    public function init($config = array())
    {
        foreach ($config as $k => $v) {
            $this->$k = $v;
        }
        /* 计算分页信息 */
        $this->totalPages = ceil($this->totalRows / $this->listRows); //总页数

        $this->nowPage = empty($_GET[$this->p]) ? 1 : intval($_GET[$this->p]);//现在行

        $this->urlParam = empty($this->urlParam) ? $_GET : $this->urlParam;//参数

        if (isset($this->urlParam[$this->p])) unset($this->urlParam[$this->p]);

        $this->url = getUrl(loadClass('core\Uri')->getPath()) . '?' . (empty($this->urlParam) ? '' : http_build_query($this->urlParam) . '&');

        return $this;
    }

    /**
     * 显示
     */
    public function show()
    {
        $html = $this->fullTagOpen;
        $html .= $this->first();
        $html .= $this->prev();
        $html .= $this->pageList();
        $html .= $this->next();
        $html .= $this->last();
        $html .= !$this->gotoPage ? "" : $this->gotoPage();
        $html .= $this->fullTagClose;
        echo $html;
    }

    /**
     * 第一页
     */
    private function first()
    {
        if ($this->nowPage > 1)
            return $this->firstTagOpen . '<a href="' . $this->url . $this->p . '=1">' . $this->firstLink . '</a>' . $this->firstTagClose;
    }

    /**
     * 上一页
     */
    private function prev()
    {
        if ($this->nowPage > 1)
            return $this->prevTagOpen . '<a href="' . $this->url . $this->p . '=' . intval($this->nowPage - 1) . '">' . $this->prevLink . '</a>' . $this->prevTagClose;
    }

    /**
     * 其他页面
     * @return string
     */
    private function pageList()
    {
        $html = '';

        $leftPage = $this->nowPage - floor($this->rollPage / 2);

        if ($leftPage <= 0) {
            $leftPage = 1;
            $rightPage = $this->rollPage + $this->nowPage;
        } else {
            $rightPage = ceil($this->rollPage / 2) + $this->nowPage;
        }
        $rightPage = $rightPage > $this->totalPages ? $this->totalPages : $rightPage;
        for ($leftPage; $leftPage <= $rightPage; $leftPage++) {
            if ($this->nowPage != $leftPage) {
                $html .= $this->otherTagOpen . '<a href="' . $this->url . $this->p . '=' . $leftPage . '">' . $leftPage . '</a>' . $this->otherTagClose;
            } else {
                $html .= $this->nowTagOpen . '<a href="' . $this->url . $this->p . '=' . $leftPage . '">' . $leftPage . '</a>' . $this->nowTagClose;
            }

        }
        return $html;
    }

    /**
     * 下一页
     */
    private function next()
    {
        if ($this->nowPage != $this->totalPages)
            return $this->nextTagOpen . '<a href="' . $this->url . $this->p . '=' . intval($this->nowPage + 1) . '">' . $this->nextLink . '</a>' . $this->nextTagClose;

    }

    /**
     * 最后一页
     */
    private function last()
    {
        if ($this->nowPage != $this->totalPages)
            return $this->lastTagOpen . '<a href="' . $this->url . $this->p . '=' . $this->totalPages . '">' . $this->lastLink . '</a>' . $this->lastTagClose;
    }

    private function gotoPage()
    {
        $html = "<select onchange='javascript:location=\"{$this->url}{$this->p}=\"+this.value'>";

        for ($i = 0; $i <= $this->totalPages; $i++) {
            if ($i == $this->nowPage) $status = 'selected';
            $html .= '<option value="' . $i . '" ' . $status . '>' . $i . '</option>';
            $status = '';
        }

        $html .= '</select>';

        return $html;
    }
}