<?php
namespace App\Core;

class Pagination
{
    public int $currentPage;
    public int $perPage;
    public int $total;
    public int $lastPage;
    public int $from;
    public int $to;
    public array $items;
    public array $queryParams;

    public function __construct(array $items, int $total, int $currentPage, int $perPage = 20)
    {
        $this->items = $items;
        $this->total = $total;
        $this->currentPage = max(1, $currentPage);
        $this->perPage = $perPage;
        $this->lastPage = (int)ceil($total / $perPage);
        $this->from = ($this->currentPage - 1) * $perPage + 1;
        $this->to = min($this->from + $perPage - 1, $total);
        $this->queryParams = $_GET;
        unset($this->queryParams['page']);
    }

    public function hasPages(): bool
    {
        return $this->lastPage > 1;
    }

    public function hasPrevious(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNext(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    public function previousPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    public function nextPage(): int
    {
        return min($this->lastPage, $this->currentPage + 1);
    }

    public function getRange(int $steps = 2): array
    {
        $range = [];
        $start = max(1, $this->currentPage - $steps);
        $end = min($this->lastPage, $this->currentPage + $steps);
        for ($i = $start; $i <= $end; $i++) {
            $range[] = $i;
        }
        return $range;
    }

    public function url(int $page): string
    {
        $params = $this->queryParams;
        $params['page'] = $page;
        return '?' . http_build_query($params);
    }

    public function render(): string
    {
        if (!$this->hasPages()) return '';

        $html = '<nav aria-label="Paginación"><ul class="pagination pagination-sm justify-content-center mb-0">';

        // Previous
        $prevClass = $this->hasPrevious() ? '' : ' disabled';
        $prevLink = $this->hasPrevious() ? $this->url($this->previousPage()) : '#';
        $html .= "<li class=\"page-item{$prevClass}\"><a class=\"page-link\" href=\"{$prevLink}\">&laquo; Anterior</a></li>";

        // Pages
        $range = $this->getRange();
        if ($range[0] > 1) {
            $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"{$this->url(1)}\">1</a></li>";
            if ($range[0] > 2) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        foreach ($range as $page) {
            $active = $page === $this->currentPage ? ' active' : '';
            $html .= "<li class=\"page-item{$active}\"><a class=\"page-link\" href=\"{$this->url($page)}\">{$page}</a></li>";
        }
        if ($range[count($range)-1] < $this->lastPage) {
            if ($range[count($range)-1] < $this->lastPage - 1) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"{$this->url($this->lastPage)}\">{$this->lastPage}</a></li>";
        }

        // Next
        $nextClass = $this->hasNext() ? '' : ' disabled';
        $nextLink = $this->hasNext() ? $this->url($this->nextPage()) : '#';
        $html .= "<li class=\"page-item{$nextClass}\"><a class=\"page-link\" href=\"{$nextLink}\">Siguiente &raquo;</a></li>";

        $html .= '</ul></nav>';
        $html .= "<p class=\"text-muted small text-center mt-1 mb-0\">Mostrando {$this->from}–{$this->to} de {$this->total} registros</p>";
        return $html;
    }
}
