<?php
/**
 * Created by yrPHP.
 * User: Quinn
 * QQ: 284843370
 * Email: quinnH@163.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 *
 */
namespace libs;

<?php
/**
 * Created by yrPHP.
 * User: Quinn
 * QQ: 284843370
 * Email: quinnH@163.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 *
 */

class ShopCart
{

    protected $singleCartContents = array();
    protected $multiCartContents = array();
    protected $error = '';
    public $saveMode = 'cookie';
    public $mallMode = true;//商城模式 true多商家 false单商家

    public function __construct($params = array())
    {
        if(isset($params['mallMode'])){
            $this->mallMode = $params['mallMode'];
        }

        if(isset($params['mallMode'])){
            $this->saveMode = $params['saveMode'];
        }


        if ($this->saveMode == 'session' && !session_id()) session_start();

        $this->contents();

    }

    /**
     * 返回一个包含了购物车中所有信息的数组
     * @return array
     */
    public function contents()
    {
        if ($this->saveMode == 'session') {
            $data = isset($_SESSION['cartContents']) ? $_SESSION['cartContents'] : array();
        } else {
            $data = isset($_COOKIE['cartContents']) ? json_decode($_COOKIE['cartContents'],true) : array();
        }

        if ($this->mallMode) {
            foreach ($data as $v) {
                foreach ($v as $kk => $vv) {
                    $this->singleCartContents[$kk] = $vv;
                }
            }
            $this->multiCartContents = $data;

        } else {
            $this->singleCartContents = $data;

        }
        return $data;
    }

    /**
     * 添加单条或多条购物车项目
     * @param array $items
     * @return bool|string
     */
    public function insert($items = array())
    {

        if (isset($items['id'])) {
            $rowId[] = $this->_insert($items);
        } elseif (is_array(reset($items))) {

            foreach ($items as $v) {
                $rowId[] = $this->_insert($v);
            }

        }

        if(!isset($rowId)) return false;
        if (in_array(false, $rowId)) {
            return false;
        }


        if ($this->mallMode) {
            $this->saveCart($this->multiCartContents);
        } else {
            $this->saveCart($this->singleCartContents);
        }

        if (!isset($rowId[1])) {
            return $rowId[0];
        } else {
            return $rowId;
        }
    }

    /**
     * 添加单条购物车项目
     * @param array $items
     * @return bool|string
     */
    protected function _insert($item = array())
    {
        if (!is_array($item) OR count($item) === 0) {
            $this->error = '插入的数据必须是数组格式';
            return false;
        }


        if (!isset($item['id'], $item['qty'], $item['price'], $item['name'])) {
            $this->error = '数组必须包含 id(产品ID),qty(商品数量),price(商品价格),name(商品名称)';
            return false;
        }

        $item['qty'] = intval($item['qty']);
        $item['price'] = (float)$item['price'];

        if (isset($item['options']) && count($item['options']) > 0) {
            $rowId = md5($item['id'] . serialize($item['options']));
        } else {
            $rowId = md5($item['id']);
        }
        $item['rowId'] = $rowId;
        $item['subtotal'] = $item['qty'] * $item['price'];

        if (isset($this->singleCartContents[$rowId])) {
            $this->singleCartContents[$rowId]['qty'] += $item['qty'];
            $this->singleCartContents[$rowId]['subtotal'] += $item['subtotal'];

        } else {
            $this->singleCartContents[$rowId] = $item;
        }


        if (isset($item['seller']) && $this->mallMode) {
            $this->multiCartContents[$item['seller']][$rowId] = $this->singleCartContents[$rowId];
        }


        return $rowId;
    }

    /**
     * 根据配置保存数据
     * @param array $cartContent
     * @return array
     */
    public function saveCart($cartContent = array())
    {
        if ($this->saveMode == 'session') {

            $_SESSION['cartContents'] = $cartContent;
        } else {

            setcookie('cartContents', json_encode($cartContent), time() + 36000,'/');

        }

    }

    /**
     * 更新购物车中的项目 必须包含 rowId
     * @param $item
     * @return bool
     */
    public function update($items = array())
    {

        $status = true;
        if (isset($items['rowId'])) {
            $status = $this->_update($items);
        } elseif (is_array(reset($items))) {

            foreach ($items as $v) {
                if ($this->_update($v) === false) {
                    $status = false;
                }
            }

        }

        if ($status === false) return false;

        if ($this->mallMode) {
            $this->saveCart($this->multiCartContents);
        } else {
            $this->saveCart($this->singleCartContents);
        }

        return true;

    }


    /**
     * 修改单条项目
     * @param $item
     * @return bool
     */
    protected function _update($item)
    {

        if (!isset($this->singleCartContents[$item['rowId']])) {
            $this->error = '数组必须包含 rowId(唯一标识符)';
            return false;
        }


        if (isset($item['qty'])) {
            $item['qty'] = intval($item['qty']);
        }

        if (isset($item['price'])) {
            $item['price'] = (float)$item['price'];
        }

        $keys = array_intersect(array_keys($this->singleCartContents[$item['rowId']]), array_keys($item));


        foreach (array_diff($keys, array('id', 'name')) as $key) {
            $this->singleCartContents[$item['rowId']][$key] = $item[$key];
        }

        $this->singleCartContents[$item['rowId']]['subtotal'] =
            $this->singleCartContents[$item['rowId']]['qty'] * $this->singleCartContents[$item['rowId']]['price'];


        if ($this->mallMode) {
            $seller = $this->singleCartContents[$item['rowId']]['seller'];
            $this->multiCartContents[$seller][$item['rowId']] = $this->singleCartContents[$item['rowId']];
        }

        return true;
    }

    /**
     * 删除一条购物车中的项目  必须包含 rowId
     * @param null $rowId
     * @return bool
     */
    public function remove($rowId = null)
    {
        if (!isset($this->singleCartContents[$rowId])) {
            return false;
        }

        if ($this->mallMode) {
            $seller = $this->singleCartContents[$rowId]['seller'];
            unset($this->multiCartContents[$seller][$rowId]);
            $this->saveCart($this->multiCartContents);
        } else {
            unset($this->singleCartContents[$rowId]);
            $this->saveCart($this->singleCartContents);
        }

        return true;
    }

    /**
     * 获得一条购物车的项目
     * @param null $rowId
     * @return bool
     */
    public function getItem($rowId = null)
    {
        if (!isset($this->singleCartContents[$rowId])) {
            return false;
        }

        return $this->singleCartContents[$rowId];
    }

    /**
     * 显示购物车中总共的项目数量
     * @return int
     */
    public function total_items()
    {
        return count($this->singleCartContents);
    }

    /**
     * 显示购物车中的总计金额
     * @return int
     */
    public function total()
    {
        $total = 0;
        foreach ($this->singleCartContents as $v) {
            $total += $v['subtotal'];
        }
        return $total;
    }

    /**
     * 根据rowId 查找商家
     * @param $key
     * @return bool|int|string
     */
    public function searchSeller($rowId)
    {
        foreach ($this->cartContents as $k => $v) {
            if (isset($v[$rowId])) {
                return $k;
            }
        }
        return false;
    }

    /**
     * 销毁购物车
     */
    public function destroy()
    {
        if ($this->saveMode == 'session') {
            unset($_SESSION['cartContents']);
        } else {
            setcookie('cartContents', 'die', time()-3600,'/');
        }
    }

    public function getError(){
        return $this->error;
    }
}
