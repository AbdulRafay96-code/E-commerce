<?php
/**
 * Product Data - Stitch House
 * Loads products from the database. Category metadata (titles, descriptions)
 * stays in PHP because it's copywriting, not per-product inventory data.
 */

// Category metadata (order drives on-screen order of sections)
$productCategories = [
    'cotton'       => ['title' => 'COTTON FABRICS',        'description' => 'High-quality cotton fabrics for comfortable everyday wear'],
    'lattha'       => ['title' => 'LATTHA FABRICS',        'description' => 'Traditional lattha fabrics for classic looks'],
    'karandi'      => ['title' => 'KARANDI FABRICS',       'description' => 'Premium karandi fabrics for luxurious comfort'],
    'boski'        => ['title' => 'BOSKI FABRICS',         'description' => 'Fine boski fabrics for elegant traditional wear'],
    'linen'        => ['title' => 'LINEN FABRICS',         'description' => 'Premium linen fabrics for breathable comfort'],
    'washandwear'  => ['title' => 'WASH AND WEAR FABRICS', 'description' => 'Low-maintenance fabrics for everyday use'],
    'silk'         => ['title' => 'SILK FABRICS',          'description' => 'Luxurious silk fabrics for special occasions'],
    'khaddar'      => ['title' => 'KHADDAR FABRICS',       'description' => 'Traditional khaddar fabrics for winter wear'],
];

// Build $products array from DB so admin edits (price, name, image, stock) take effect live
$products = [];
foreach ($productCategories as $key => $meta) {
    $products[$key] = ['title' => $meta['title'], 'description' => $meta['description'], 'items' => []];
}

if (isset($conn) && $conn) {
    $rs = $conn->query(
        "SELECT id, name, price, image, category, stock_quantity
         FROM products
         ORDER BY category, id"
    );
    if ($rs) {
        while ($row = $rs->fetch_assoc()) {
            $cat = $row['category'];
            if (!isset($products[$cat])) continue; // unknown category — skip
            $products[$cat]['items'][] = [
                'id'             => $row['id'],
                'name'           => $row['name'],
                'price'          => (float)$row['price'],
                'image'          => $row['image'],
                'stock_quantity' => (int)$row['stock_quantity'],
            ];
        }
    }
}

/**
 * Render a product section
 */
function renderProductSection($sectionKey, $section) {
    $sectionId = $sectionKey . '-section';
    ?>
    <section class="product-section" id="<?php echo $sectionId; ?>">
        <div class="container">
            <div class="section-header reveal">
                <h2><?php echo htmlspecialchars($section['title']); ?></h2>
                <p><?php echo htmlspecialchars($section['description']); ?></p>
            </div>
            <div class="product-grid">
                <?php foreach ($section['items'] as $idx => $product): ?>
                <?php
                    $stock = isset($product['stock_quantity']) ? (int)$product['stock_quantity'] : null;
                    if ($stock === null && function_exists('getProductStock')) {
                        $stock = getProductStock($product['id']);
                    }
                    $isOutOfStock = ($stock !== null && $stock <= 0);
                    $isLowStock   = ($stock !== null && $stock > 0 && $stock <= 5);
                ?>
                <div class="shop-product-card reveal reveal-delay-<?php echo $idx + 1; ?><?php echo $isOutOfStock ? ' out-of-stock' : ''; ?>">
                    <div class="product-image">
                        <img src="images/<?php echo htmlspecialchars($product['image']); ?>"
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             loading="lazy">
                        <?php if ($isOutOfStock): ?>
                            <span class="stock-badge out-of-stock-badge">Out of Stock</span>
                        <?php elseif ($isLowStock): ?>
                            <span class="stock-badge low-stock-badge">Only <?php echo (int)$stock; ?> left</span>
                        <?php endif; ?>
                        <div class="product-actions">
                            <button class="btn-add-to-order"
                                    data-id="<?php echo htmlspecialchars($product['id']); ?>"
                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-price="<?php echo $product['price']; ?>"
                                    data-image="<?php echo htmlspecialchars($product['image']); ?>"
                                    data-section="<?php echo $sectionId; ?>"
                                    <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-cart"></i>
                                <?php echo $isOutOfStock ? 'Out of Stock' : 'Add to Order'; ?>
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="product-meta">
                            <span class="price">PKR. <?php echo number_format($product['price']); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}
