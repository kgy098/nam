<?php
/* cn_product.php */

/* 리스트 (is_active=1 기본) */
function select_product_list($start=0, $num=CN_PAGE_NUM, $params = []) {
    $where = " where is_active = 1 ";

    if (!empty($params['type']))
        $where .= " and type = '{$params['type']}' ";

    if (isset($params['active']) && $params['active'] !== '')  // 강제로 활성/비활성 검색할 때
        $where .= " and is_active = ".intval($params['active'])." ";

    if (!empty($params['name']))
        $where .= " and name like '%{$params['name']}%' ";

    $sql = "select *
            from cn_product
            {$where}
            order by sort_order asc, id desc
            limit {$start}, {$num}";

    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

/* 단건 조회 (is_active = 1 조건 적용) */
function select_product_one($id) {
    $sql = "select *
            from cn_product
            where id = ".intval($id)."
              and is_active = 1";
    return sql_fetch($sql);
}

/* 등록 */
function insert_product($mb_id, $name, $type, $description, $base_amount, $period_type, $sort_order=0) {
    $sql = "insert into cn_product
            set mb_id = '{$mb_id}',
                name = '{$name}',
                type = '{$type}',
                description = '{$description}',
                base_amount = ".intval($base_amount).",
                period_type = '{$period_type}',
                is_active = 1,
                sort_order = ".intval($sort_order);
    return sql_query($sql);
}

/* 수정 – 전달된 값만 업데이트 */
function update_product($id, $fields) {
    $set = [];

    foreach ($fields as $key => $value) {
        if ($value === null) continue;

        // 숫자형 필드
        if (in_array($key, ['base_amount', 'is_active', 'sort_order'])) {
            $set[] = "{$key} = ".intval($value);
        } else {
            $set[] = "{$key} = '{$value}'";
        }
    }

    if (empty($set)) return false;

    $set_sql = implode(", ", $set);

    $sql = "update cn_product
            set {$set_sql}
            where id = ".intval($id)."
              and is_active = 1";

    return sql_query($sql);
}

/* 🔥 Soft Delete: is_active = 0 */
function soft_delete_product($id) {
    $sql = "update cn_product
            set is_active = 0
            where id = ".intval($id);
    return sql_query($sql);
}
?>
