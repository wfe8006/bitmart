<?php	
$user_id = (int)$this->auth->get_user()->id;
$limit = $this->cfg['item_per_page'];
$offset = ((int)Arr::get($_GET, 'page', 1) - 1) * $limit;
$view = View::factory(TEMPLATE . '/my/order/index');
if ($_SERVER['REQUEST_URI'] == '/my/purchase')
{
	$view->url = 'purchase';
	$view->menu = 'my_menu';
	$query = 'buyer_id';
	$view->title = I18n::get('purchase_history');
	$view->column = I18n::get('seller');
}
else
{
	$view->url = 'order';
	$view->menu = 'menu';
	$query = 'seller_id';
	$view->title = I18n::get('sales_order');
	$view->column = I18n::get('customer');
}
$view->order_obj = DB::query(Database::SELECT, "SELECT o.id AS order_id, buyer_id, u.username AS username, data, date_part('epoch', submitted)::int AS submitted, os.name AS order_status FROM public.order o LEFT JOIN public.user u ON o.buyer_id = u.id LEFT JOIN order_status os ON (o.data->>'order_status')::integer = os.id WHERE $query = :user_id ORDER BY o.id DESC LIMIT :limit OFFSET :offset")
->param(':user_id', $user_id)
->param(':limit', $limit)
->param(':offset', $offset)
->execute();
$order_count = DB::query(Database::SELECT, "SELECT COUNT(o.id) AS total FROM public.order o WHERE $query = :user_id")
->param(':user_id', $user_id)
->execute();

$pagination = Pagination::factory(array(
	'query_string'   => 'page',
	'total_items'    => $order_count[0]['total'],
	'items_per_page' => $limit,
	'style'          => 'classic',
	'auto_hide'      => TRUE
));
$view->pagination = $pagination->render();
$view->cfg = $this->cfg;
$this->template->content = $view;