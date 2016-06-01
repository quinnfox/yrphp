<?php

/**
 * Created by yrPHP.
 * User: Quinn
 * QQ: 284843370
 * Email: quinnH@163.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 *
 */
class Cart {

	protected $singleCartContents = array();
	protected $multiCartContents = array();
	protected $error = '';
	public $saveMode = 'session';
	public $mallMode = true; //商城模式 true多商家 false单商家

	public function __construct($params = array()) {
		if (isset($params['mallMode'])) {
			$this->mallMode = $params['mallMode'];
		}

		if (isset($params['mallMode'])) {
			$this->saveMode = $params['saveMode'];
		}

		if ($this->saveMode == 'session' && !session_id()) {
			session_start();
		}

		if (isset($_SESSION['cartContents']) || isset($_COOKIE['cartContents'])) {
			$this->contents();
		}

	}

	/**
	 * $mallMode;//商城模式 true多商家(二维数组) false单商家（一维数组）
	 * @param null $mallMode
	 */
	function getContents($mallMode = null) {
		$mallMode = is_null($mallMode) ? $this->mallMode : $mallMode;
		if ($mallMode) {
			return $this->multiCartContents;

		} else {
			return $this->singleCartContents;
		}
	}

	/**
	 * 返回一个包含了购物车中所有信息的数组
	 * @return array
	 */
	public function contents() {
		$data = '';
		if ($this->saveMode == 'session' && isset($_SESSION['cartContents'])) {
			$data = $_SESSION['cartContents'];
		} else if (isset($_COOKIE['cartContents'])) {
			$data = json_decode($_COOKIE['cartContents'], true);
		}
		$data = empty($data) ? array() : $data;

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
	public function insert($items = array()) {
		if (isset($items['id'])) {
			$rowId[] = $this->_insert($items);
		} elseif (is_array(reset($items))) {

			foreach ($items as $v) {
				$rowId[] = $this->_insert($v);
			}

		}

		if (!isset($rowId)) {
			return false;
		}

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
	protected function _insert($item = array()) {
		if (!is_array($item) OR count($item) === 0) {
			$this->error = '插入的数据必须是数组格式';
			return false;
		}

		if (!isset($item['id'], $item['qty'], $item['price'], $item['name'])) {
			$this->error = '数组必须包含 id(产品ID),qty(商品数量),price(商品价格),name(商品名称)';
			return false;
		}

		$item['qty'] = intval($item['qty']);
		$item['price'] = (float) $item['price'];

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

		if ($this->mallMode) {
			if (isset($item['seller'])) {
				$this->multiCartContents[$item['seller']][$rowId] = $this->singleCartContents[$rowId];
			} else {
				$this->error = 'seller(卖家标识ID) 不能为空';
				return false;
			}
		}

		return $rowId;
	}

	/**
	 * 根据配置保存数据
	 * @param array $cartContent
	 * @return array
	 */
	public function saveCart($cartContent = null) {

		if ($this->saveMode == 'session') {

			$_SESSION['cartContents'] = $cartContent;
		} else {

			setcookie('cartContents', json_encode($cartContent), time() + 36000, '/');

		}

	}

	/**
	 * 更新购物车中的项目 必须包含 rowId
	 * @param $item
	 * @return bool
	 */
	public function update($items = array()) {

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

		if ($status === false) {
			return false;
		}

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
	protected function _update($item) {
		if (!isset($this->singleCartContents[$item['rowId']])) {
			$this->error = '数组必须包含 rowId(唯一标识符)';
			return false;
		}

		if (isset($item['qty'])) {
			$item['qty'] = intval($item['qty']);
		}

		if (isset($item['price'])) {
			$item['price'] = (float) $item['price'];
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
	 * @param null|array $rowId
	 * @return bool
	 */
	public function remove($rowId = null) {

		if (!is_array($rowId)) {
			$rowId = array($rowId);
		}

		foreach ($rowId as $v) {
			if (!isset($this->singleCartContents[$v])) {
				continue;
			}
			if ($this->mallMode) {
				$seller = $this->singleCartContents[$v]['seller'];

				unset($this->multiCartContents[$seller][$v]);

				if (count($this->multiCartContents[$seller]) == 0) {
					unset($this->multiCartContents[$seller]);
				}

				$this->saveCart($this->multiCartContents);

				unset($this->singleCartContents[$v]);

			} else {

				unset($this->singleCartContents[$v]);
				$this->saveCart($this->singleCartContents);
			}
		}

		return $this->totalItems();
	}

	/**
	 * 获得一条购物车的项目
	 * @param null $rowId
	 * @return bool
	 */
	public function getItem($rowId = null) {
		if (!$rowId) {
			return false;
		}

		if (!isset($this->singleCartContents[$rowId])) {
			return false;
		}

		return $this->singleCartContents[$rowId];
	}

	/**
	 * 显示购物车中总共的项目数量
	 * @return int
	 */
	public function totalItems() {
		return count($this->singleCartContents);
	}

	/**
	 * 显示购物车中的总计金额
	 * @return int
	 */
	public function total($seller = null) {
		$total = 0;
		if ($this->mallMode && !is_null($seller)) {
			if (isset($this->multiCartContents[$seller])) {
				foreach ($this->multiCartContents[$seller] as $v) {
					$total += $v['subtotal'];
				}
			}
		} else {
			foreach ($this->singleCartContents as $v) {
				$total += $v['subtotal'];
			}
		}
		return $total;
	}

	/**
	 * 根据rowId 查找商家
	 * @param $key
	 * @return bool|int|string
	 */
	public function searchSeller($rowId) {
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
	public function destroy() {
		if ($this->saveMode == 'session') {
			unset($_SESSION['cartContents']);
		} else {
			setcookie('cartContents', 'die', time() - 3600, '/');
		}

	}

	public function getError() {
		return $this->error;
	}
}