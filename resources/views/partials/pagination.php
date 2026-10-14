<?php
// resources/views/partials/pagination.php
if (isset($totalPages) && $totalPages > 1):
    $queryParams = $_GET;
?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <!-- Previous Page Link -->
        <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
            <?php 
                $queryParams['page'] = $currentPage - 1;
                $prevUrl = '?' . http_build_query($queryParams);
            ?>
            <a class="page-link" href="<?= $prevUrl ?>">&laquo; Previous</a>
        </li>

        <!-- Numbered Page Links -->
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php 
                $queryParams['page'] = $i;
                $pageUrl = '?' . http_build_query($queryParams);
            ?>
            <li class="page-item <?= ($i === $currentPage) ? 'active' : '' ?>">
                <a class="page-link" href="<?= $pageUrl ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <!-- Next Page Link -->
        <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
            <?php 
                $queryParams['page'] = $currentPage + 1;
                $nextUrl = '?' . http_build_query($queryParams);
            ?>
            <a class="page-link" href="<?= $nextUrl ?>">Next &raquo;</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

