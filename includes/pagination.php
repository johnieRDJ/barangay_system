<?php

if(!function_exists('pagination_per_page')){
    function pagination_per_page(): int
    {
        $allowed = [10, 20, 30, 40, 50];
        $perPage = intval($_GET['per_page'] ?? 10);

        return in_array($perPage, $allowed, true) ? $perPage : 10;
    }
}

if(!function_exists('pagination_page')){
    function pagination_page(): int
    {
        return max(1, intval($_GET['page'] ?? 1));
    }
}

if(!function_exists('pagination_total')){
    function pagination_total(mysqli $conn, string $sql, string $types = '', array $params = []): int
    {
        $row = db_select_one($conn, $sql, $types, $params);

        return $row ? intval($row['total'] ?? 0) : 0;
    }
}

if(!function_exists('pagination_state')){
    function pagination_state(mysqli $conn, string $countSql, string $types = '', array $params = []): array
    {
        $perPage = pagination_per_page();
        $total = pagination_total($conn, $countSql, $types, $params);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = min(pagination_page(), $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'total' => $total,
            'per_page' => $perPage,
            'page' => $page,
            'total_pages' => $totalPages,
            'offset' => $offset,
            'limit_sql' => ' LIMIT ' . $perPage . ' OFFSET ' . $offset,
        ];
    }
}

if(!function_exists('pagination_url')){
    function pagination_url(int $page, int $perPage): string
    {
        $query = $_GET;
        unset($query['delete'], $query['cancelled'], $query['confirmation'], $query['updated']);
        $query['page'] = $page;
        $query['per_page'] = $perPage;

        return strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query($query);
    }
}

if(!function_exists('render_pagination')){
    function render_pagination(array $state, string $label = 'records'): void
    {
        $total = intval($state['total']);
        $page = intval($state['page']);
        $perPage = intval($state['per_page']);
        $totalPages = intval($state['total_pages']);
        $start = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
        $end = min($total, $page * $perPage);
        $allowed = [10, 20, 30, 40, 50];
        $query = $_GET;
        unset($query['page'], $query['per_page'], $query['delete'], $query['cancelled'], $query['confirmation'], $query['updated']);
        ?>
        <div class="pagination-bar">
            <div class="pagination-summary">
                Showing <?php echo $start; ?>-<?php echo $end; ?> of <?php echo $total; ?> <?php echo htmlspecialchars($label); ?>
            </div>

            <form method="GET" class="pagination-size-form">
                <?php foreach($query as $key => $value): ?>
                    <?php if(is_array($value)) continue; ?>
                    <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                <?php endforeach; ?>
                <input type="hidden" name="page" value="1">
                <label for="per_page">Rows</label>
                <select name="per_page" id="per_page" onchange="this.form.submit()">
                    <?php foreach($allowed as $option): ?>
                        <option value="<?php echo $option; ?>" <?php echo $perPage === $option ? 'selected' : ''; ?>>
                            <?php echo $option; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <div class="pagination-links">
                <a class="<?php echo $page <= 1 ? 'disabled' : ''; ?>" href="<?php echo $page <= 1 ? '#' : htmlspecialchars(pagination_url($page - 1, $perPage)); ?>">Previous</a>
                <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                <a class="<?php echo $page >= $totalPages ? 'disabled' : ''; ?>" href="<?php echo $page >= $totalPages ? '#' : htmlspecialchars(pagination_url($page + 1, $perPage)); ?>">Next</a>
            </div>
        </div>
        <?php
    }
}
