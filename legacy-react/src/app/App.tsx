import { useState, useCallback, useEffect } from "react";
import { Toaster } from "sonner";
import {
  Navbar, Footer, HomePage, AuthPage, EmailVerifyPage, CatalogPage,
  ProductDetailPage, CartPage, CheckoutPage, BuyerDashboard,
} from "./user/UserPages";
import {
  AdminLayout, AdminDashboard, AdminOrders, AdminTransactions,
  AdminReports, AdminProducts, AdminStock,
} from "./admin/AdminPages";
import * as Shared from "./shared";

const { PRODUCTS } = Shared;
type Page = Shared.Page;
type OfflineTransaction = Shared.OfflineTransaction;
type StockItem = Shared.StockItem;
type UserRole = Shared.UserRole;
type Product = Shared.Product;
type Order = Shared.Order;

export default function App() {
  const [page, setPage] = useState<Page>("home");
  const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);
  const [products, setProducts] = useState<Product[]>(() => { try { return JSON.parse(localStorage.getItem("lintang_products") || JSON.stringify(PRODUCTS)); } catch { return PRODUCTS; } });
  useEffect(() => { localStorage.setItem("lintang_products", JSON.stringify(products)); }, [products]);
  const [cart, setCart] = useState<{ product: Product; quantity: number }[]>(() => {
    try { return JSON.parse(localStorage.getItem("lintang_cart") || "[]"); } catch { return []; }
  });
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [userRole, setUserRole] = useState<UserRole>(null);
  const [orders, setOrders] = useState<Order[]>(() => {
    try { return JSON.parse(localStorage.getItem("lintang_orders") || "[]"); } catch { return []; }
  });

  const INITIAL_STOCKS: StockItem[] = PRODUCTS.map((p, i) => ({ ...p, stock: [100, 100, 100, 100, 100][i] }));
  const [stocks, setStocks] = useState<StockItem[]>(() => {
    try { return JSON.parse(localStorage.getItem("lintang_stocks_v2") || JSON.stringify(INITIAL_STOCKS)); } catch { return INITIAL_STOCKS; }
  });
  const [offlineTrx, setOfflineTrx] = useState<OfflineTransaction[]>(() => {
    try { return JSON.parse(localStorage.getItem("lintang_trx") || "[]"); } catch { return []; }
  });

  useEffect(() => {
    localStorage.setItem("lintang_cart", JSON.stringify(cart));
  }, [cart]);

  useEffect(() => { localStorage.setItem("lintang_orders", JSON.stringify(orders)); }, [orders]);
  useEffect(() => { localStorage.setItem("lintang_stocks_v2", JSON.stringify(stocks)); }, [stocks]);
  useEffect(() => { localStorage.setItem("lintang_trx", JSON.stringify(offlineTrx)); }, [offlineTrx]);

  const addToCart = useCallback((product: Product, quantity: number) => {
    setCart(prev => {
      const existing = prev.find(i => i.product.id === product.id);
      if (existing) return prev.map(i => i.product.id === product.id ? { ...i, quantity } : i);
      return [...prev, { product, quantity }];
    });
  }, []);

  const clearCart = useCallback(() => setCart([]), []);
  const removeCheckedOutItems = useCallback((checkedOut: { product: Product; quantity: number }[]) => {
    const ids = new Set(checkedOut.map(i => i.product.id));
    setCart(prev => prev.filter(i => !ids.has(i.product.id)));
  }, []);
  const removeFromCart = useCallback((productId: number) => setCart(prev => prev.filter(i => i.product.id !== productId)), []);
  const updateCartQty = useCallback((productId: number, qty: number) => setCart(prev => prev.map(i => i.product.id === productId ? { ...i, quantity: qty } : i)), []);
  const addOrder = useCallback((order: Order) => setOrders(prev => [order, ...prev]), []);

  const resetData = useCallback(() => {
    setOrders([]);
    setCart([]);
    setCheckoutCart([]);
    setOfflineTrx([]);
    setStocks(PRODUCTS.map((p, i) => ({ ...p, stock: [100, 100, 100, 100, 100][i] })));
    ["lintang_cart","lintang_orders","lintang_stocks_v2","lintang_trx"].forEach(k => localStorage.removeItem(k));
  }, []);
  const [checkoutCart, setCheckoutCart] = useState<{ product: Product; quantity: number }[]>([]);

  const handleLogout = () => {
    setIsLoggedIn(false);
    setUserRole(null);
    setPage("home");
  };

  const isAdminPage = page.startsWith("admin");

  // Admin pages use full-page layout with sidebar
  if (isAdminPage && isLoggedIn && userRole === "admin") {
    return (
      <div style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
        <AdminLayout page={page} setPage={setPage} onLogout={handleLogout} orders={orders}>
          {page === "admin-dashboard" && <AdminDashboard setPage={setPage} orders={orders} trx={offlineTrx} stocks={stocks} onReset={resetData} />}
          {page === "admin-orders" && <AdminOrders orders={orders} setOrders={setOrders} />}
          {page === "admin-transactions" && <AdminTransactions trx={offlineTrx} setTrx={setOfflineTrx} stocks={stocks} setStocks={setStocks} />}
          {page === "admin-products" && <AdminProducts products={products} setProducts={setProducts} stocks={stocks} setStocks={setStocks} />}
          {page === "admin-stock" && <AdminStock stocks={stocks} setStocks={setStocks} />}
          {page === "admin-reports" && <AdminReports orders={orders} trx={offlineTrx} />}
        </AdminLayout>
      </div>
    );
  }

  // Full-screen pages (no navbar/footer)
  if (page === "auth" || page === "email-verify") {
    return (
      <div style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
        {page === "auth" && <AuthPage setPage={setPage} setIsLoggedIn={setIsLoggedIn} setUserRole={setUserRole} />}
        {page === "email-verify" && <EmailVerifyPage setPage={setPage} setIsLoggedIn={setIsLoggedIn} setUserRole={setUserRole} />}
      </div>
    );
  }

  return (
    <div style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }} className="min-h-screen bg-background flex flex-col">
      <Toaster
        position="bottom-right"
        toastOptions={{
          classNames: {
            toast: "!bg-card !border-border !text-foreground !shadow-lg",
            title: "!text-foreground !font-semibold",
            description: "!text-muted-foreground",
            icon: "!text-primary",
            success: "!border-l-4 !border-l-primary",
          },
        }}
      />
      <Navbar
        page={page} setPage={setPage}
        cartCount={cart.length}
        isLoggedIn={isLoggedIn}
        userRole={userRole}
        onLogout={handleLogout}
      />
      <main className="flex-1">
        {page === "home" && <HomePage products={products} setPage={setPage} setSelectedProduct={setSelectedProduct} addToCart={addToCart} isLoggedIn={isLoggedIn} />}
        {page === "catalog" && <CatalogPage products={products} setPage={setPage} setSelectedProduct={setSelectedProduct} addToCart={addToCart} isLoggedIn={isLoggedIn} />}
        {page === "product-detail" && <ProductDetailPage product={selectedProduct} setPage={setPage} addToCart={addToCart} isLoggedIn={isLoggedIn} setCheckoutCart={setCheckoutCart} />}
        {page === "cart" && (
          <CartPage
            cart={cart}
            setPage={setPage}
            removeFromCart={removeFromCart}
            updateCartQty={updateCartQty}
            onCheckout={(selected) => { setCheckoutCart(selected); setPage("checkout"); }}
          />
        )}
        {page === "checkout" && <CheckoutPage setPage={setPage} cart={checkoutCart} removeCheckedOutItems={removeCheckedOutItems} addOrder={addOrder} />}
        {page === "buyer-dashboard" && <BuyerDashboard setPage={setPage} orders={orders} />}
      </main>
      {page !== "buyer-dashboard" && <Footer setPage={setPage} />}
    </div>
  );
}
