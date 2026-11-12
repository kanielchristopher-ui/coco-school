<?php
/**
 * ============================================================
 *  Pagination
 *  Simple, reusable pagination helper.
 * ============================================================
 */

declare(strict_types=1);

class Pagination
{
    protected int $page;
    protected int $limit;
    protected int $total;
    protected int $pages;

    /**
     * @param int $total total number of records
     * @param int $page  current page
     * @param int $limit records per page
     */
    public function __construct(int $total, int $page = 1, int $limit = PAGINATION_LIMIT)
    {
        $this->total = max(0, $total);
        $this->limit = max(1, $limit);
        $this->pages = (int) max(1, ceil($this->total / $this->limit));
        $this->page  = max(1, min($page, $this->pages));
    }

    public function page(): int { return $this->page; }
    public function pages(): int { return $this->pages; }
    public function total(): int { return $this->total; }
    public function limit(): int { return $this->limit; }

    /**
     * SQL OFFSET for the current page.
     */
    public function offset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    /**
     * Generate a link for a given page number, preserving current GET params.
     */
    public function link(int $page): string
    {
        $params = $_GET;
        $params['page'] = $page;
        return '?' . http_build_query($params);
    }

    /**
     * Render the pagination control.
     */
    public function render(string $cssClass = 'pagination pagination-sm mb-0'): string
    {
        if ($this->pages <= 1) {
            return '';
        }

        $html = '<nav><ul class="' . $cssClass . '">';

        // Previous
        $prev = $this->page - 1;
        $html .= $this->page > 1
            ? '<li class="page-item"><a class="page-link" href="' . e($this->link($prev)) . '">Prev</a></li>'
            : '<li class="page-item disabled"><span class="page-link">Prev</span></li>';

        // Page numbers
        $start = max(1, $this->page - 2);
        $end   = min($this->pages, $this->page + 2);
        if ($start > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . e($this->link(1)) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
        }
        for ($i = $start; $i <= $end; $i++) {
            $html .= $i === $this->page
                ? '<li class="page-item active"><span class="page-link">' . $i . '</span></li>'
                : '<li class="page-item"><a class="page-link" href="' . e($this->link($i)) . '">' . $i . '</a></li>';
        }
        if ($end < $this->pages) {
            if ($end < $this->pages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . e($this->link($this->pages)) . '">' . $this->pages . '</a></li>';
        }

        // Next
        $next = $this->page + 1;
        $html .= $this->page < $this->pages
            ? '<li class="page-item"><a class="page-link" href="' . e($this->link($next)) . '">Next</a></li>'
            : '<li class="page-item disabled"><span class="page-link">Next</span></li>';

        $html .= '</ul></nav>';
        return $html;
    }
}
