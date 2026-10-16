import { useState, useRef, useCallback, useEffect } from "react";
import { toast, Toaster } from "sonner";
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer
} from "recharts";
import {
  ShoppingCart, User, Menu, X, ChevronRight, Check, Copy,
  Upload, Package, TrendingUp, Clock, CheckCircle, Truck,
  ShoppingBag, BarChart2, LogOut, Eye, Edit, Trash2,
  Plus, Minus, ChevronDown, AlertTriangle, Star, MapPin,
  Phone, Mail, ArrowRight, Shield, Layers, Search, Bell,
  FileText, Archive, RefreshCw
} from "lucide-react";

// ─── Types ───────────────────────────────────────────────────────────────────
import * as Shared from "../shared";

const { PRODUCTS, PROVINCES, ORDER_STATUSES, MOCK_ORDERS, MONTHLY_DATA, fmt, fmtDate, statusColor, Btn, StatusBadge, Input, Select } = Shared;
type Page = Shared.Page;
type OfflineTransaction = Shared.OfflineTransaction;
type StockItem = Shared.StockItem;
type UserRole = Shared.UserRole;
type Product = Shared.Product;
type OrderItem = Shared.OrderItem;
type Order = Shared.Order;
type OrderStatus = Shared.OrderStatus;

export function AdminLayout({ children, page, setPage, onLogout, orders }: {
  children: React.ReactNode; page: Page; setPage: (p: Page) => void; onLogout: () => void; orders: Order[];
}) {
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [notifOpen, setNotifOpen] = useState(false);

  const notifications = [
    ...orders.filter(o => o.status === "Menunggu Konfirmasi").map(o => ({
      id: o.id, type: "confirm" as const,
      title: "Pesanan menunggu konfirmasi",
      desc: `${o.buyer} — ${fmt(o.total + o.shippingCost)}`,
      date: o.date, target: "admin-orders" as Page,
    })),
    ...orders.filter(o => o.status === "Pembayaran DP Disetujui").map(o => ({
      id: o.id + "-dp", type: "dp" as const,
      title: "DP disetujui, siap diproduksi",
      desc: `${o.buyer} — ${o.items.map(i => i.product.name).join(", ")}`,
      date: o.date, target: "admin-orders" as Page,
    })),
  ].slice(0, 8);

  const unreadCount = notifications.length;
  const menu = [
    { icon: <BarChart2 className="w-4 h-4" />, label: "Dashboard", target: "admin-dashboard" as Page },
    { icon: <FileText className="w-4 h-4" />, label: "Pesanan", target: "admin-orders" as Page },
    { icon: <Package className="w-4 h-4" />, label: "Produk", target: "admin-products" as Page },
    { icon: <ShoppingBag className="w-4 h-4" />, label: "Transaksi Offline", target: "admin-transactions" as Page },
    { icon: <Archive className="w-4 h-4" />, label: "Stok Offline", target: "admin-stock" as Page },
    { icon: <BarChart2 className="w-4 h-4" />, label: "Laporan", target: "admin-reports" as Page },
  ];
  return (
    <div className="flex min-h-screen bg-muted/40">
      <aside className={`${sidebarOpen ? "w-56" : "w-16"} bg-sidebar border-r border-sidebar-border transition-all duration-300 flex flex-col flex-shrink-0`}>
        <div className="flex items-center gap-2.5 p-4 border-b border-sidebar-border h-16">
          <div className="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0">
            <span className="text-white font-bold text-xs" style={{ fontFamily: "'Playfair Display', serif" }}>L</span>
          </div>
          {sidebarOpen && <span className="font-bold text-sm text-foreground truncate" style={{ fontFamily: "'Playfair Display', serif" }}>Lintang Bakery</span>}
        </div>
        <nav className="flex flex-col gap-1 p-2 flex-1">
          {menu.map(item => (
            <button key={item.label} onClick={() => setPage(item.target)}
              className={`flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all cursor-pointer w-full text-left ${page === item.target ? "bg-primary text-primary-foreground" : "text-sidebar-foreground hover:bg-sidebar-accent hover:text-foreground"}`}>
              {item.icon}
              {sidebarOpen && <span>{item.label}</span>}
            </button>
          ))}
        </nav>
        <div className="p-2 border-t border-sidebar-border">
          <button onClick={onLogout}
            className="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-muted-foreground hover:bg-destructive/10 hover:text-destructive transition-all cursor-pointer w-full">
            <LogOut className="w-4 h-4 flex-shrink-0" />
            {sidebarOpen && <span>Keluar</span>}
          </button>
        </div>
      </aside>
      <div className="flex-1 flex flex-col min-w-0">
        <header className="h-16 bg-card border-b border-border flex items-center justify-between px-5 flex-shrink-0">
          <div className="flex items-center gap-3">
            <button onClick={() => setSidebarOpen(!sidebarOpen)} className="p-2 rounded-lg hover:bg-muted cursor-pointer transition-colors">
              <Menu className="w-5 h-5 text-muted-foreground" />
            </button>
            <h1 className="font-semibold text-foreground">{menu.find(m => m.target === page)?.label || "Admin"}</h1>
          </div>
          <div className="flex items-center gap-3">
            <div className="relative">
              <button onClick={() => setNotifOpen(!notifOpen)} className="p-2 rounded-lg hover:bg-muted cursor-pointer relative transition-colors">
                <Bell className="w-5 h-5 text-muted-foreground" />
                {unreadCount > 0 && (
                  <span className="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-destructive text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1">
                    {unreadCount}
                  </span>
                )}
              </button>

              {notifOpen && (
                <>
                  <div className="fixed inset-0 z-40" onClick={() => setNotifOpen(false)} />
                  <div className="absolute right-0 top-11 z-50 w-80 bg-card border border-border rounded-2xl shadow-xl overflow-hidden">
                    <div className="flex items-center justify-between px-4 py-3 border-b border-border">
                      <h3 className="font-semibold text-foreground text-sm">Notifikasi</h3>
                      {unreadCount > 0 && <span className="text-xs text-primary font-medium">{unreadCount} belum ditindak</span>}
                    </div>
                    <div className="max-h-80 overflow-y-auto">
                      {notifications.length === 0 ? (
                        <div className="px-4 py-10 text-center text-muted-foreground">
                          <Bell className="w-8 h-8 mx-auto mb-2 opacity-30" />
                          <p className="text-sm">Tidak ada notifikasi baru</p>
                        </div>
                      ) : notifications.map(n => (
                        <button key={n.id} onClick={() => { setPage(n.target); setNotifOpen(false); }}
                          className={`w-full text-left px-4 py-3 border-b border-border last:border-0 hover:bg-muted/50 transition-colors cursor-pointer`}>
                          <div className="flex items-start gap-3">
                            <div className={`w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 ${n.type === "confirm" ? "bg-amber-100 text-amber-600" : "bg-blue-100 text-blue-600"}`}>
                              {n.type === "confirm" ? <Clock className="w-4 h-4" /> : <CheckCircle className="w-4 h-4" />}
                            </div>
                            <div className="flex-1 min-w-0">
                              <p className="text-sm font-medium text-foreground">{n.title}</p>
                              <p className="text-xs text-muted-foreground truncate mt-0.5">{n.desc}</p>
                              <p className="text-xs text-muted-foreground mt-1">{fmtDate(n.date)}</p>
                            </div>
                            <ChevronRight className="w-4 h-4 text-muted-foreground flex-shrink-0 mt-1" />
                          </div>
                        </button>
                      ))}
                    </div>
                    {notifications.length > 0 && (
                      <div className="px-4 py-3 border-t border-border">
                        <button onClick={() => { setPage("admin-orders"); setNotifOpen(false); }}
                          className="w-full text-center text-sm text-primary font-medium hover:underline cursor-pointer">
                          Lihat semua pesanan →
                        </button>
                      </div>
                    )}
                  </div>
                </>
              )}
            </div>
            <div className="flex items-center gap-2 text-sm">
              <div className="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                <User className="w-4 h-4 text-primary" />
              </div>
              <span className="text-foreground font-medium hidden sm:block">Admin</span>
            </div>
          </div>
        </header>
        <main className="flex-1 overflow-auto p-6">{children}</main>
      </div>
    </div>
  );
}

// ─── Page: Admin Dashboard ─────────────────────────────────────────────────────
export function BestSellerChart({ orders, trx }: { orders: Order[]; trx: OfflineTransaction[] }) {
  const sales: Record<string, number> = {};
  [...orders.flatMap(o => o.items), ...trx.flatMap(t => t.items)].forEach(i => {
    sales[i.product.name] = (sales[i.product.name] || 0) + i.quantity;
  });
  const data = PRODUCTS.map(p => ({ name: p.name.split(" ").slice(0, 2).join(" "), total: sales[p.name] || 0 })).sort((a, b) => b.total - a.total);
  if (data.every(d => d.total === 0)) return <p className="text-sm text-muted-foreground text-center py-8">Belum ada data penjualan.</p>;
  return (
    <ResponsiveContainer width="100%" height={200}>
      <BarChart id="chart-bestseller" data={data} layout="vertical" margin={{ top: 0, right: 20, bottom: 0, left: 10 }}>
        <CartesianGrid strokeDasharray="3 3" stroke="rgba(139,69,19,0.08)" horizontal={false} />
        <XAxis type="number" tick={{ fontSize: 11, fill: "#8B6F5E" }} />
        <YAxis type="category" dataKey="name" tick={{ fontSize: 11, fill: "#2C1810" }} width={90} />
        <Tooltip contentStyle={{ background: "#fff", border: "1px solid rgba(139,69,19,0.15)", borderRadius: "12px", fontSize: "12px" }} cursor={{ fill: "rgba(139,69,19,0.05)" }} formatter={(v) => [`${v} bal`, "Terjual"]} />
        <Bar dataKey="total" fill="#8B4513" radius={[0, 6, 6, 0]} name="Terjual" />
      </BarChart>
    </ResponsiveContainer>
  );
}

export function TrendChart() {
  return (
    <ResponsiveContainer width="100%" height={200}>
      <BarChart id="chart-trend" data={MONTHLY_DATA} margin={{ top: 5, right: 5, bottom: 5, left: -10 }}>
        <CartesianGrid strokeDasharray="3 3" stroke="rgba(139,69,19,0.1)" />
        <XAxis dataKey="bulan" tick={{ fontSize: 11, fill: "#8B6F5E" }} />
        <YAxis tick={{ fontSize: 11, fill: "#8B6F5E" }} />
        <Tooltip contentStyle={{ background: "#fff", border: "1px solid rgba(139,69,19,0.15)", borderRadius: "12px", fontSize: "13px" }} cursor={{ fill: "rgba(139,69,19,0.06)" }} />
        <Bar dataKey="pesanan" fill="#D2691E" radius={[6, 6, 0, 0]} name="Pesanan" />
      </BarChart>
    </ResponsiveContainer>
  );
}

export function AdminDashboard({ setPage, orders, trx, stocks, onReset }: { setPage: (p: Page) => void; orders: Order[]; trx: OfflineTransaction[]; stocks: StockItem[]; onReset: () => void }) {
  const today = new Date().toISOString().split("T")[0];
  const thisMonth = today.slice(0, 7);

  const todayOrders = orders.filter(o => o.date === today);
  const waitingConfirm = orders.filter(o => o.status === "Menunggu Konfirmasi");
  const inProcess = orders.filter(o => ["Sedang Diproduksi", "Siap Dikirim", "Sedang Dikirim"].includes(o.status));
  const monthRevenue = orders
    .filter(o => o.date.startsWith(thisMonth) && o.status === "Selesai/Lunas")
    .reduce((sum, o) => sum + o.total + o.shippingCost, 0);

  const cards = [
    {
      label: "Pesanan Masuk Hari Ini",
      value: String(todayOrders.length),
      sub: todayOrders.length === 0 ? "Belum ada pesanan hari ini" : `${todayOrders.length} pesanan baru`,
      icon: <ShoppingBag className="w-6 h-6" />,
      color: "text-blue-600 bg-blue-50 border-blue-100",
    },
    {
      label: "Menunggu Konfirmasi",
      value: String(waitingConfirm.length),
      sub: waitingConfirm.length > 0 ? "Perlu tindakan segera" : "Semua sudah dikonfirmasi",
      icon: <Clock className="w-6 h-6" />,
      color: "text-amber-600 bg-amber-50 border-amber-100",
    },
    {
      label: "Sedang Diproses",
      value: String(inProcess.length),
      sub: "Produksi & pengiriman aktif",
      icon: <TrendingUp className="w-6 h-6" />,
      color: "text-purple-600 bg-purple-50 border-purple-100",
    },
    {
      label: "Pendapatan Bulan Ini",
      value: monthRevenue > 0 ? fmt(monthRevenue) : "Rp 0",
      sub: "Dari pesanan Selesai/Lunas",
      icon: <CheckCircle className="w-6 h-6" />,
      color: "text-green-600 bg-green-50 border-green-100",
    },
  ];

  return (
    <div>
      <div className="flex items-start justify-between mb-6 gap-4">
        <div>
          <h2 className="text-xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Selamat datang kembali, Admin 👋</h2>
          <p className="text-sm text-muted-foreground mt-1">Ringkasan aktivitas Lintang Bakery hari ini.</p>
        </div>
        <button
          onClick={() => {
            if (window.confirm("Reset semua data pesanan dan keranjang ke kondisi awal? Aksi ini tidak bisa dibatalkan.")) {
              onReset();
            }
          }}
          className="flex items-center gap-2 px-3 py-2 text-xs font-medium text-muted-foreground border border-border rounded-xl hover:bg-destructive/10 hover:text-destructive hover:border-destructive/30 transition-colors cursor-pointer flex-shrink-0"
        >
          <RefreshCw className="w-3.5 h-3.5" /> Reset Data Testing
        </button>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {cards.map(c => (
          <div key={c.label} className={`bg-card border rounded-2xl p-5 hover:shadow-md transition-shadow ${c.color.includes("border") ? "border-" + c.color.split("border-")[1].split(" ")[0] : "border-border"}`}>
            <div className={`w-11 h-11 rounded-xl flex items-center justify-center mb-3 ${c.color.split(" ").slice(0, 2).join(" ")}`}>
              {c.icon}
            </div>
            <p className="text-sm font-semibold text-foreground mb-1">{c.label}</p>
            <p className="text-2xl font-bold text-foreground">{c.value}</p>
            <p className="text-xs text-muted-foreground mt-1">{c.sub}</p>
          </div>
        ))}
      </div>

      {/* Best Seller + Tren */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div className="bg-card border border-border rounded-2xl p-5">
          <h3 className="font-semibold text-foreground mb-5" style={{ fontFamily: "'Playfair Display', serif" }}>Produk Terlaris</h3>
          <BestSellerChart orders={orders} trx={trx} />
        </div>
        <div className="bg-card border border-border rounded-2xl p-5">
          <h3 className="font-semibold text-foreground mb-5" style={{ fontFamily: "'Playfair Display', serif" }}>Tren Pesanan per Bulan</h3>
          <TrendChart />
        </div>
      </div>

      {/* Quick actions */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        {[
          { label: "Kelola Pesanan", sub: `${orders.filter(o => o.status === "Menunggu Konfirmasi").length} menunggu`, target: "admin-orders", icon: <FileText className="w-5 h-5" /> },
          { label: "Catat Kasir", sub: "Penjualan toko offline", target: "admin-transactions", icon: <ShoppingBag className="w-5 h-5" /> },
          { label: "Lihat Stok", sub: `${stocks?.filter(s => s.stock < 50).length ?? 0} produk hampir habis`, target: "admin-stock", icon: <Archive className="w-5 h-5" /> },
          { label: "Laporan Bulan Ini", sub: "Rekap penjualan", target: "admin-reports", icon: <BarChart2 className="w-5 h-5" /> },
        ].map(item => (
          <button key={item.label} onClick={() => setPage(item.target as Page)}
            className="bg-card border border-border rounded-2xl p-4 text-left hover:shadow-md hover:border-primary/30 transition-all cursor-pointer group">
            <div className="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary mb-3 group-hover:bg-primary group-hover:text-white transition-all">
              {item.icon}
            </div>
            <p className="font-semibold text-foreground text-sm">{item.label}</p>
            <p className="text-xs text-muted-foreground mt-0.5">{item.sub}</p>
          </button>
        ))}
      </div>
    </div>
  );
}

// ─── Page: Admin Orders ───────────────────────────────────────────────────────
export function AdminOrders({ orders, setOrders }: { orders: Order[]; setOrders: React.Dispatch<React.SetStateAction<Order[]>> }) {
  const [filter, setFilter] = useState<OrderStatus | "Semua">("Semua");
  const [detail, setDetail] = useState<Order | null>(null);

  const filtered = filter === "Semua" ? orders : orders.filter(o => o.status === filter);

  const updateStatus = (id: string, status: OrderStatus) => {
    setOrders(prev => prev.map(o => o.id === id ? { ...o, status } : o));
    if (detail?.id === id) setDetail(prev => prev ? { ...prev, status } : null);
  };

  if (detail) {
    return (
      <div>
        <button onClick={() => setDetail(null)} className="flex items-center gap-2 text-muted-foreground hover:text-foreground text-sm mb-6 cursor-pointer">
          <ChevronRight className="w-4 h-4 rotate-180" /> Kembali ke Daftar Pesanan
        </button>
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">
          <div className="lg:col-span-2 flex flex-col gap-5">
            <div className="bg-card border border-border rounded-2xl p-5">
              <div className="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h2 className="font-bold text-lg text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Pesanan #{detail.id}</h2>
                <StatusBadge status={detail.status} />
              </div>
              <div className="grid grid-cols-2 gap-3 text-sm mb-4">
                <div><span className="text-muted-foreground block text-xs mb-0.5">Nama Pembeli</span><span className="font-medium">{detail.buyer}</span></div>
                <div><span className="text-muted-foreground block text-xs mb-0.5">No. Telepon</span><span className="font-medium">{detail.phone}</span></div>
                <div><span className="text-muted-foreground block text-xs mb-0.5">Alamat</span><span className="font-medium">{detail.address}</span></div>
                <div><span className="text-muted-foreground block text-xs mb-0.5">Kota</span><span className="font-medium">{detail.city}, {detail.province}</span></div>
                <div><span className="text-muted-foreground block text-xs mb-0.5">Tanggal Pesan</span><span className="font-medium">{fmtDate(detail.date)}</span></div>
                <div><span className="text-muted-foreground block text-xs mb-0.5">Tipe Bayar</span><span className="font-medium capitalize">{detail.paymentType === "dp" ? "DP 65%" : "Bayar Penuh"}</span></div>
              </div>

              <h3 className="font-semibold text-sm text-foreground mb-3 border-t border-border pt-4">Produk Dipesan</h3>
              {detail.items.map(item => (
                <div key={item.product.id} className="flex gap-3 items-center bg-muted/50 rounded-xl p-3 mb-2">
                  <img src={item.product.image} alt={item.product.name} className="w-14 h-14 rounded-xl object-cover bg-muted" />
                  <div className="flex-1">
                    <p className="font-medium text-sm">{item.product.name}</p>
                    <p className="text-xs text-muted-foreground">{item.quantity} bal × {fmt(item.product.pricePerBal)}</p>
                  </div>
                  <p className="font-bold text-primary">{fmt(item.subtotal)}</p>
                </div>
              ))}

              <div className="border-t border-border pt-4 mt-2">
                <h3 className="font-semibold text-sm text-foreground mb-3">Rincian Pembayaran</h3>
                <div className="bg-muted/50 rounded-xl p-4 flex flex-col gap-2 text-sm">
                  <div className="flex justify-between"><span className="text-muted-foreground">Total Harga Produk</span><span>{fmt(detail.total)}</span></div>
                  <div className="flex justify-between"><span className="text-muted-foreground">Ongkir</span><span>{fmt(detail.shippingCost)}</span></div>
                  {detail.paymentType === "dp" && (
                    <>
                      <div className="flex justify-between font-semibold text-accent"><span>DP 65% (sudah dibayar)</span><span>{fmt(detail.dp)}</span></div>
                      <div className="flex justify-between text-muted-foreground"><span>Sisa Pelunasan</span><span>{fmt(detail.remaining)}</span></div>
                    </>
                  )}
                  <div className="flex justify-between font-bold border-t border-border pt-2">
                    <span>Total</span><span className="text-primary">{fmt(detail.total + detail.shippingCost)}</span>
                  </div>
                </div>
              </div>
            </div>

            {/* Bukti Pembayaran */}
            <div className="bg-card border border-border rounded-2xl p-5">
              <h3 className="font-semibold text-foreground text-sm mb-3">Bukti Pembayaran</h3>
              {detail.paymentProof ? (
                <div className="rounded-xl overflow-hidden border border-border">
                  <img
                    src={detail.paymentProof}
                    alt="Bukti pembayaran"
                    className="w-full max-h-72 object-contain bg-muted"
                  />
                  <div className="flex items-center gap-2 px-4 py-2 bg-primary/5 border-t border-border">
                    <CheckCircle className="w-4 h-4 text-primary" />
                    <p className="text-sm text-primary font-medium">Bukti pembayaran sudah diupload pembeli</p>
                  </div>
                </div>
              ) : (
                <div className="bg-muted/50 rounded-xl h-32 flex items-center justify-center text-muted-foreground border border-dashed border-border">
                  <div className="text-center">
                    <Upload className="w-7 h-7 mx-auto mb-1.5 opacity-40" />
                    <p className="text-sm">Belum ada bukti pembayaran</p>
                  </div>
                </div>
              )}
            </div>

            {/* Action Buttons */}
            {detail.status === "Menunggu Konfirmasi" && (
              <div className="bg-card border border-border rounded-2xl p-5">
                <h3 className="font-semibold text-foreground text-sm mb-4">Tindakan</h3>
                <div className="flex flex-wrap gap-3">
                  <Btn onClick={() => updateStatus(detail.id, detail.paymentType === "dp" ? "Pembayaran DP Disetujui" : "Sedang Diproduksi")}>
                    <Check className="w-4 h-4" /> {detail.paymentType === "dp" ? "Konfirmasi Pembayaran DP" : "Konfirmasi Pembayaran Lunas"}
                  </Btn>
                  <Btn variant="danger" onClick={() => {
                    setOrders(prev => prev.filter(o => o.id !== detail.id));
                    setDetail(null);
                  }}>
                    <X className="w-4 h-4" /> Tolak Pesanan
                  </Btn>
                </div>
              </div>
            )}
          </div>

          <div className="flex flex-col gap-4">
            <div className="bg-card border border-border rounded-2xl p-5">
              <h3 className="font-semibold text-foreground text-sm mb-4">Alur Status</h3>
              {ORDER_STATUSES.map((s, i) => {
                const currentIdx = ORDER_STATUSES.indexOf(detail.status);
                const done = i <= currentIdx;
                const active = i === currentIdx;
                return (
                  <div key={s} className="flex items-start gap-3 pb-3 last:pb-0">
                    <div className="flex flex-col items-center flex-shrink-0">
                      <div className={`w-6 h-6 rounded-full flex items-center justify-center text-xs border-2 ${done ? "bg-primary border-primary" : "bg-card border-border"}`}>
                        {done ? <Check className="w-3 h-3 text-white" /> : <span className="text-muted-foreground">{i + 1}</span>}
                      </div>
                      {i < ORDER_STATUSES.length - 1 && <div className={`w-0.5 h-4 mt-0.5 ${done ? "bg-primary" : "bg-border"}`} />}
                    </div>
                    <p className={`text-xs pt-0.5 ${active ? "text-primary font-semibold" : done ? "text-foreground font-medium" : "text-muted-foreground"}`}>{s}</p>
                  </div>
                );
              })}

              {detail.status !== "Selesai/Lunas" && (
                <div className="mt-4 pt-4 border-t border-border">
                  <label className="text-xs font-medium text-muted-foreground block mb-2">Ubah Status:</label>
                  <div className="relative">
                    <select
                      value={detail.status}
                      onChange={e => updateStatus(detail.id, e.target.value as OrderStatus)}
                      className="w-full px-3 py-2 pr-8 rounded-xl border border-border bg-input-background text-sm focus:outline-none focus:ring-2 focus:ring-ring/40 appearance-none cursor-pointer"
                    >
                      {ORDER_STATUSES.map(s => <option key={s} value={s}>{s}</option>)}
                    </select>
                    <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div>
      <div className="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h2 className="text-xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Manajemen Pesanan</h2>
      </div>

      {/* Filter */}
      <div className="flex flex-wrap gap-2 mb-6">
        {(["Semua", ...ORDER_STATUSES] as const).map(s => (
          <button key={s} onClick={() => setFilter(s as typeof filter)}
            className={`px-3 py-1.5 rounded-full text-xs font-medium border transition-colors cursor-pointer ${filter === s ? "bg-primary text-primary-foreground border-primary" : "bg-card border-border text-muted-foreground hover:border-primary/40 hover:text-foreground"}`}>
            {s}
          </button>
        ))}
      </div>

      <div className="bg-card border border-border rounded-2xl overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-border bg-muted/30">
                {["No. Pesanan", "Pembeli", "Produk", "Total", "Status", "Aksi"].map(h => (
                  <th key={h} className={`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide ${h === 'Total' ? 'text-right' : ''}`}>{h}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {filtered.map((o, i) => (
                <tr key={o.id} className={`border-b border-border hover:bg-muted/30 transition-colors ${i % 2 === 0 ? "" : "bg-muted/10"}`}>
                  <td className="px-4 py-3 font-mono text-xs text-foreground">{o.id}</td>
                  <td className="px-4 py-3">
                    <p className="font-medium text-foreground">{o.buyer}</p>
                    <p className="text-xs text-muted-foreground">{fmtDate(o.date)}</p>
                  </td>
                  <td className="px-4 py-3 text-muted-foreground text-xs">
                    {o.items.map(i => `${i.product.name} (${i.quantity} bal)`).join(", ")}
                  </td>
                  <td className="px-4 py-3 font-semibold text-primary text-right">{fmt(o.total + o.shippingCost)}</td>
                  <td className="px-4 py-3"><StatusBadge status={o.status} /></td>
                  <td className="px-4 py-3">
                    <button onClick={() => setDetail(o)}
                      className="flex items-center gap-1.5 text-xs text-primary font-medium hover:underline cursor-pointer">
                      <Eye className="w-3.5 h-3.5" /> Detail
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        {filtered.length === 0 && (
          <div className="py-16 text-center text-muted-foreground">
            <Package className="w-12 h-12 mx-auto mb-3 opacity-30" />
            <p>Tidak ada pesanan dengan status ini.</p>
          </div>
        )}
      </div>
    </div>
  );
}

// ─── Page: Admin Transactions ─────────────────────────────────────────────────
export function AdminTransactions({ trx, setTrx, stocks, setStocks }: {
  trx: OfflineTransaction[];
  setTrx: React.Dispatch<React.SetStateAction<OfflineTransaction[]>>;
  stocks: StockItem[];
  setStocks: React.Dispatch<React.SetStateAction<StockItem[]>>;
}) {
  const [showForm, setShowForm] = useState(false);
  const [date, setDate] = useState(new Date().toISOString().split("T")[0]);
  const [buyerName, setBuyerName] = useState("");
  const [notes, setNotes] = useState("");
  const [paymentMethod, setPaymentMethod] = useState<"cash" | "qris-tf">("cash");
  const [items, setItems] = useState<{ productId: number; quantity: number }[]>([{ productId: PRODUCTS[0].id, quantity: 1 }]);
  const [error, setError] = useState("");

  const addItem = () => setItems(prev => [...prev, { productId: PRODUCTS[0].id, quantity: 1 }]);
  const removeItem = (i: number) => setItems(prev => prev.filter((_, idx) => idx !== i));
  const updateItem = (i: number, field: "productId" | "quantity", val: number) =>
    setItems(prev => prev.map((it, idx) => idx === i ? { ...it, [field]: val } : it));

  const handleSave = () => {
    for (const it of items) {
      const stock = stocks.find(s => s.id === it.productId);
      if (stock && it.quantity > stock.stock) {
        setError(`Stok ${stock.name} tidak cukup (tersisa ${stock.stock} bal).`);
        return;
      }
    }
    setError("");
    const newTrx: OfflineTransaction = {
      id: `TRX-${Date.now()}`,
      date,
      buyerName,
      paymentMethod,
      notes,
      items: items.map(it => {
        const p = PRODUCTS.find(p => p.id === it.productId)!;
        return { product: p, quantity: it.quantity, subtotal: p.pricePerBal * it.quantity };
      }),
      total: items.reduce((a, it) => {
        const p = PRODUCTS.find(p => p.id === it.productId)!;
        return a + p.pricePerBal * it.quantity;
      }, 0),
    };
    setTrx(prev => [newTrx, ...prev]);
    setStocks(prev => prev.map(s => {
      const sold = items.find(it => it.productId === s.id);
      return sold ? { ...s, stock: Math.max(0, s.stock - sold.quantity) } : s;
    }));
    setBuyerName(""); setNotes(""); setPaymentMethod("cash"); setItems([{ productId: PRODUCTS[0].id, quantity: 1 }]);
    setShowForm(false);
  };

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Transaksi Offline</h2>
        <Btn size="sm" onClick={() => setShowForm(true)}><Plus className="w-4 h-4" /> Tambah Transaksi</Btn>
      </div>

      {showForm && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4" style={{ backdropFilter: "blur(8px)", backgroundColor: "rgba(44,24,16,0.5)" }} onClick={() => setShowForm(false)}>
          <div className="bg-card rounded-2xl border border-border shadow-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto" onClick={e => e.stopPropagation()}>
            <div className="flex items-center justify-between mb-5">
              <h3 className="font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Tambah Transaksi</h3>
              <button onClick={() => setShowForm(false)} className="p-1.5 rounded-lg hover:bg-muted cursor-pointer"><X className="w-4 h-4" /></button>
            </div>
            <div className="flex flex-col gap-4">
              <Input label="Tanggal" type="date" value={date} onChange={setDate} required />
              <div>
                <div className="flex items-center justify-between mb-2">
                  <label className="text-sm font-medium text-foreground">Produk <span className="text-destructive">*</span></label>
                  <button onClick={addItem} className="text-xs text-primary hover:underline cursor-pointer flex items-center gap-1"><Plus className="w-3 h-3" /> Tambah produk</button>
                </div>
                <div className="flex flex-col gap-2">
                  {items.map((it, i) => (
                    <div key={i} className="flex gap-2 items-center">
                      <div className="relative flex-1">
                        <select value={it.productId} onChange={e => updateItem(i, "productId", Number(e.target.value))}
                          className="w-full px-3 py-2 pr-8 rounded-lg border border-border bg-input-background text-sm appearance-none focus:outline-none focus:ring-2 focus:ring-ring/40 focus:border-primary cursor-pointer">
                          {PRODUCTS.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                        </select>
                        <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
                      </div>
                      <input type="number" value={it.quantity} min={1}
                        onChange={e => updateItem(i, "quantity", Math.max(1, parseInt(e.target.value) || 1))}
                        className="w-20 text-center px-2 py-2 rounded-lg border border-border bg-input-background text-sm focus:outline-none focus:ring-2 focus:ring-ring/40"
                      />
                      <span className="text-xs text-muted-foreground flex-shrink-0">bal</span>
                      {items.length > 1 && (
                        <button onClick={() => removeItem(i)} className="p-1.5 rounded-lg hover:bg-destructive/10 hover:text-destructive text-muted-foreground cursor-pointer">
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      )}
                    </div>
                  ))}
                </div>
              </div>
              <div className="bg-muted/50 rounded-xl p-3 text-sm">
                <div className="flex justify-between font-semibold">
                  <span>Total</span>
                  <span className="text-primary">{fmt(items.reduce((a, it) => {
                    const p = PRODUCTS.find(p => p.id === it.productId)!;
                    return a + p.pricePerBal * it.quantity;
                  }, 0))}</span>
                </div>
              </div>
              <div>
                <label className="text-sm font-medium text-foreground block mb-2">Metode Pembayaran <span className="text-destructive">*</span></label>
                <div className="flex gap-2">
                  {([["cash","Tunai"],["qris-tf","QRIS / Transfer"]] as const).map(([val, label]) => (
                    <button key={val} type="button" onClick={() => setPaymentMethod(val)}
                      className={`flex-1 py-2 rounded-xl text-sm font-medium border transition-all cursor-pointer ${paymentMethod === val ? "bg-primary text-primary-foreground border-primary" : "bg-card border-border text-muted-foreground hover:border-primary/40"}`}>
                      {label}
                    </button>
                  ))}
                </div>
              </div>
              <Input label="Catatan (opsional)" value={notes} onChange={setNotes} placeholder="Misal: nomor referensi, dll" />
              {error && <p className="text-xs text-destructive bg-destructive/10 px-3 py-2 rounded-lg">{error}</p>}
              <div className="flex gap-3 mt-1">
                <Btn variant="outline" className="flex-1" onClick={() => setShowForm(false)}>Batal</Btn>
                <Btn className="flex-1" onClick={handleSave}>Simpan Transaksi</Btn>
              </div>
            </div>
          </div>
        </div>
      )}

      <div className="bg-card border border-border rounded-2xl overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-border bg-muted/30">
                {["Tanggal", "Produk", "Bayar", "Total", "Catatan"].map(h => (
                  <th key={h} className={`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide ${h === 'Total' ? 'text-right' : ''}`}>{h}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {trx.length === 0 ? (
                <tr><td colSpan={6} className="px-4 py-16 text-center text-muted-foreground">
                  <Archive className="w-10 h-10 mx-auto mb-2 opacity-30" />
                  <p>Belum ada transaksi offline.</p>
                </td></tr>
              ) : trx.map((t, i) => (
                <tr key={t.id} className={`border-b border-border last:border-0 hover:bg-muted/20 ${i % 2 === 0 ? "" : "bg-muted/10"}`}>
                  <td className="px-4 py-3 text-sm whitespace-nowrap">{fmtDate(t.date)}</td>
                  
                  <td className="px-4 py-3 text-xs text-muted-foreground">{t.items.map(i => `${i.product.name} (${i.quantity} bal)`).join(", ")}</td>
                  <td className="px-4 py-3">
                    <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border ${
                      t.paymentMethod === "cash" ? "bg-amber-50 text-amber-700 border-amber-200" : "bg-blue-50 text-blue-700 border-blue-200"
                    }`}>
                      {t.paymentMethod === "cash" ? "Tunai" : "QRIS / Transfer"}
                    </span>
                  </td>
                  <td className="px-4 py-3 font-semibold text-primary text-right">{fmt(t.total)}</td>
                  <td className="px-4 py-3 text-xs text-muted-foreground">{t.notes || "—"}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

// ─── Page: Admin Reports ───────────────────────────────────────────────────────
export function AdminReports({ orders, trx }: { orders: Order[]; trx: OfflineTransaction[] }) {
  const [filterType, setFilterType] = useState<"all" | "month" | "date" | "range">("month");
  const currentMonth = new Date().toISOString().slice(0, 7);
  const todayDate = new Date().toISOString().split("T")[0];
  const [filterMonth, setFilterMonth] = useState(currentMonth);
  const [filterDate, setFilterDate] = useState(todayDate);
  const [filterStart, setFilterStart] = useState(todayDate);
  const [filterEnd, setFilterEnd] = useState(todayDate);

  const months = Array.from(new Set([
    ...orders.map(o => o.date.slice(0, 7)),
    ...trx.map(t => t.date.slice(0, 7)),
  ])).sort().reverse();
  
  const filteredOrders = orders.filter(o => {
    if (filterType === "all") return true;
    if (filterType === "month") return o.date.startsWith(filterMonth);
    if (filterType === "date") return o.date === filterDate;
    if (filterType === "range") return o.date >= filterStart && o.date <= filterEnd;
    return true;
  });
  
  const filteredTrx = trx.filter(t => {
    if (filterType === "all") return true;
    if (filterType === "month") return t.date.startsWith(filterMonth);
    if (filterType === "date") return t.date === filterDate;
    if (filterType === "range") return t.date >= filterStart && t.date <= filterEnd;
    return true;
  });

  const totalOnline = filteredOrders.reduce((a, o) => a + o.total + o.shippingCost, 0);
  const totalOffline = filteredTrx.reduce((a, t) => a + t.total, 0);

  const productSales: Record<string, number> = {};
  [...filteredOrders.flatMap(o => o.items), ...filteredTrx.flatMap(t => t.items)].forEach(item => {
    productSales[item.product.name] = (productSales[item.product.name] || 0) + item.quantity;
  });

  const monthLabel = (m: string) => {
    if (!m) return "";
    const [y, mo] = m.split("-");
    const names = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agt","Sep","Okt","Nov","Des"];
    return `${names[parseInt(mo) - 1]} ${y}`;
  };

  const chartData = Object.entries(productSales)
    .map(([name, total]) => ({ name, total }))
    .sort((a, b) => b.total - a.total);
    
  const topProduct = chartData[0];

  const handlePrint = () => {
    window.print();
  };

  return (
    <div className="report-container">
      <div className="flex flex-wrap items-center justify-between gap-4 mb-6 print:hidden">
        <h2 className="text-xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Laporan Penjualan</h2>
        <Btn size="sm" onClick={handlePrint}><FileText className="w-4 h-4" /> Cetak / Download PDF</Btn>
      </div>
      
      <div className="hidden print:block mb-8 border-b-2 border-primary pb-4">
        <h1 className="text-3xl font-bold text-primary mb-2" style={{ fontFamily: "'Playfair Display', serif" }}>Lintang Bakery</h1>
        <p className="text-sm text-foreground">Jl. Leuwi Anyar No. 18 RT 001 RW 003 Babakan, Jl. Kalangsari, Sukamanah, Kec. Cipedes, Kab. Tasikmalaya, Jawa Barat 46131</p>
        <h2 className="text-xl font-bold mt-4">Laporan Penjualan</h2>
        <p className="text-sm">
          Periode: {
            filterType === "all" ? "Semua Waktu" :
            filterType === "month" ? monthLabel(filterMonth) :
            filterType === "date" ? fmtDate(filterDate) :
            `${fmtDate(filterStart)} - ${fmtDate(filterEnd)}`
          }
        </p>
      </div>

      <div className="bg-card border border-border rounded-2xl p-5 mb-6 print:hidden">
        <h3 className="font-semibold text-sm mb-4">Filter Periode</h3>
        <div className="flex flex-wrap gap-4 items-end">
          <div className="flex flex-col gap-1.5">
            <label className="text-xs font-medium">Tipe Filter</label>
            <select value={filterType} onChange={e => setFilterType(e.target.value as any)}
              className="px-3 py-2 rounded-lg border border-border text-sm focus:ring-2 focus:ring-primary/40">
              <option value="all">Semua Waktu</option>
              <option value="month">Per Bulan</option>
              <option value="date">Per Tanggal</option>
              <option value="range">Rentang Kustom</option>
            </select>
          </div>
          
          {filterType === "month" && (
            <div className="flex flex-col gap-1.5">
              <label className="text-xs font-medium">Pilih Bulan</label>
              <select value={filterMonth} onChange={e => setFilterMonth(e.target.value)}
                className="px-3 py-2 rounded-lg border border-border text-sm focus:ring-2 focus:ring-primary/40">
                {months.length === 0 && <option value={currentMonth}>{monthLabel(currentMonth)}</option>}
                {months.map(m => <option key={m} value={m}>{monthLabel(m)}</option>)}
              </select>
            </div>
          )}
          
          {filterType === "date" && (
            <div className="flex flex-col gap-1.5">
              <label className="text-xs font-medium">Pilih Tanggal</label>
              <input type="date" value={filterDate} onChange={e => setFilterDate(e.target.value)}
                className="px-3 py-2 rounded-lg border border-border text-sm focus:ring-2 focus:ring-primary/40" />
            </div>
          )}
          
          {filterType === "range" && (
            <>
              <div className="flex flex-col gap-1.5">
                <label className="text-xs font-medium">Dari Tanggal</label>
                <input type="date" value={filterStart} onChange={e => setFilterStart(e.target.value)}
                  className="px-3 py-2 rounded-lg border border-border text-sm focus:ring-2 focus:ring-primary/40" />
              </div>
              <div className="flex flex-col gap-1.5">
                <label className="text-xs font-medium">Sampai Tanggal</label>
                <input type="date" value={filterEnd} onChange={e => setFilterEnd(e.target.value)}
                  className="px-3 py-2 rounded-lg border border-border text-sm focus:ring-2 focus:ring-primary/40" />
              </div>
            </>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        {[
          { label: "Total Pendapatan", value: fmt(totalOnline + totalOffline), sub: "Online + Offline", color: "text-green-600 bg-green-50" },
          { label: "Pendapatan Online", value: fmt(totalOnline), sub: `${filteredOrders.length} pesanan`, color: "text-blue-600 bg-blue-50" },
          { label: "Pendapatan Offline", value: fmt(totalOffline), sub: `${filteredTrx.length} transaksi`, color: "text-primary bg-primary/10" },
          { label: "Produk Terlaris", value: topProduct ? topProduct.name.split(" ").slice(0, 2).join(" ") : "-", sub: topProduct ? `${topProduct.total} bal` : "Belum ada data", color: "text-amber-600 bg-amber-50" },
        ].map(c => (
          <div key={c.label} className="bg-card border border-border rounded-2xl p-5">
            <div className={`w-10 h-10 rounded-xl flex items-center justify-center mb-3 ${c.color} print:border print:border-border`}>
              <FileText className="w-5 h-5" />
            </div>
            <p className="text-xs text-muted-foreground mb-1">{c.label}</p>
            <p className="text-xl font-bold text-foreground truncate">{c.value}</p>
            <p className="text-xs text-muted-foreground mt-0.5">{c.sub}</p>
          </div>
        ))}
      </div>

      {chartData.length > 0 && (
        <div className="bg-card border border-border rounded-2xl p-5 mb-6">
          <h3 className="font-semibold text-foreground text-sm mb-4" style={{ fontFamily: "'Playfair Display', serif" }}>Grafik Produk Terjual</h3>
          <ResponsiveContainer width="100%" height={300}>
            <BarChart data={chartData.slice(0, 10)} margin={{ top: 20, right: 30, left: 20, bottom: 5 }}>
              <CartesianGrid strokeDasharray="3 3" vertical={false} />
              <XAxis dataKey="name" tick={{ fontSize: 11 }} interval={0} angle={-15} textAnchor="end" height={60} />
              <YAxis />
              <Tooltip formatter={(v) => [`${v} bal`, 'Terjual']} />
              <Bar dataKey="total" fill="#8B4513" radius={[4, 4, 0, 0]} name="Terjual" />
            </BarChart>
          </ResponsiveContainer>
        </div>
      )}

      <div className="grid grid-cols-1 xl:grid-cols-2 gap-5">
        <div className="bg-card border border-border rounded-2xl overflow-hidden">
          <div className="px-5 py-4 border-b border-border flex items-center justify-between">
            <h3 className="font-semibold text-foreground text-sm" style={{ fontFamily: "'Playfair Display', serif" }}>Pesanan Online</h3>
            <span className="text-xs text-muted-foreground">{filteredOrders.length} pesanan</span>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead><tr className="border-b border-border bg-muted/20">
                {["Tanggal","Pembeli","Total","Status"].map(h => (
                  <th key={h} className={`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide ${h === 'Total' ? 'text-right' : ''}`}>{h}</th>
                ))}
              </tr></thead>
              <tbody>
                {filteredOrders.length === 0
                  ? <tr><td colSpan={4} className="px-4 py-8 text-center text-muted-foreground text-sm">Tidak ada pesanan online.</td></tr>
                  : filteredOrders.map(o => (
                    <tr key={o.id} className="border-b border-border last:border-0 hover:bg-muted/20">
                      <td className="px-4 py-3 whitespace-nowrap">{fmtDate(o.date)}</td>
                      <td className="px-4 py-3 font-medium">{o.buyer}</td>
                      <td className="px-4 py-3 font-semibold text-primary text-right whitespace-nowrap">{fmt(o.total + o.shippingCost)}</td>
                      <td className="px-4 py-3"><StatusBadge status={o.status} /></td>
                    </tr>
                  ))
                }
              </tbody>
            </table>
          </div>
        </div>

        <div className="bg-card border border-border rounded-2xl overflow-hidden">
          <div className="px-5 py-4 border-b border-border flex items-center justify-between">
            <h3 className="font-semibold text-foreground text-sm" style={{ fontFamily: "'Playfair Display', serif" }}>Transaksi Offline</h3>
            <span className="text-xs text-muted-foreground">{filteredTrx.length} transaksi</span>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead><tr className="border-b border-border bg-muted/20">
                {["Tanggal","Total","Catatan"].map(h => (
                  <th key={h} className={`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide ${h === 'Total' ? 'text-right' : ''}`}>{h}</th>
                ))}
              </tr></thead>
              <tbody>
                {filteredTrx.length === 0
                  ? <tr><td colSpan={3} className="px-4 py-8 text-center text-muted-foreground text-sm">Tidak ada transaksi offline.</td></tr>
                  : filteredTrx.map(t => (
                    <tr key={t.id} className="border-b border-border last:border-0 hover:bg-muted/20">
                      <td className="px-4 py-3 whitespace-nowrap">{fmtDate(t.date)}</td>
                      
                      <td className="px-4 py-3 font-semibold text-primary text-right whitespace-nowrap">{fmt(t.total)}</td>
                      <td className="px-4 py-3 text-xs text-muted-foreground truncate max-w-[100px]">{t.notes || "—"}</td>
                    </tr>
                  ))
                }
              </tbody>
            </table>
          </div>
        </div>
      </div>
      
      <div className="hidden print:block mt-12 text-center text-sm text-muted-foreground">
        Dicetak pada: {new Date().toLocaleString('id-ID')}
      </div>
    </div>
  );
}

// ─── Page: Admin Products ─────────────────────────────────────────────────────
export function AdminProducts({ products, setProducts, stocks, setStocks }: { products: Product[]; setProducts: React.Dispatch<React.SetStateAction<Product[]>>; stocks: StockItem[]; setStocks: React.Dispatch<React.SetStateAction<StockItem[]>> }) {
  const [showForm, setShowForm] = useState(false);
  const [editing, setEditing] = useState<Product | null>(null);
  const [nama, setNama] = useState("");
  const [deskripsi, setDeskripsi] = useState("");
  const [harga, setHarga] = useState("");
  const [fotoUrl, setFotoUrl] = useState("");
  const [salesChannel, setSalesChannel] = useState<"online" | "offline" | "both">("online");
  const fotoRef = useRef<HTMLInputElement>(null);
  const [deleteTarget, setDeleteTarget] = useState<Product | null>(null);

  const openAdd = () => { setEditing(null); setNama(""); setDeskripsi(""); setHarga(""); setFotoUrl(""); setSalesChannel("online"); setShowForm(true); };
  const openEdit = (p: Product) => { setEditing(p); setNama(p.name); setDeskripsi(p.description); setHarga(String(p.pricePerBal)); setFotoUrl(p.image); setSalesChannel(p.salesChannel || "online"); setShowForm(true); };
  const save = () => {
    const finalImage = fotoUrl || (editing?.image ?? PRODUCTS[0].image);
    if (editing) {
      setProducts(prev => prev.map(p => p.id === editing.id ? { ...p, name: nama, description: deskripsi, pricePerBal: parseInt(harga), image: finalImage, salesChannel } : p));
      if ((salesChannel === "offline" || salesChannel === "both") && !stocks.some(s => s.id === editing.id)) {
        setStocks(prev => [...prev, { id: editing.id, name: nama, description: deskripsi, pricePerBal: parseInt(harga), image: finalImage, category: "Kue Kering", stock: 0 }]);
      }
      toast.success("Produk berhasil diperbarui", { description: nama });
    } else {
      const newP: Product = { id: Date.now(), name: nama, description: deskripsi, pricePerBal: parseInt(harga), image: finalImage, category: "Kue Kering", salesChannel };
      if (salesChannel === "offline" || salesChannel === "both") {
        setStocks(prev => [...prev, { ...newP, stock: 0 }]);
      }
      setProducts(prev => [...prev, newP]);
      toast.success("Produk berhasil ditambahkan", { description: nama });
    }
    setShowForm(false);
  };
  const confirmDelete = () => {
    if (!deleteTarget) return;
    setProducts(prev => prev.filter(p => p.id !== deleteTarget.id));
    toast.success("Produk berhasil dihapus", { description: deleteTarget.name });
    setDeleteTarget(null);
  };

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Manajemen Produk</h2>
        <Btn size="sm" onClick={openAdd}><Plus className="w-4 h-4" /> Tambah Produk</Btn>
      </div>

      {/* Delete confirmation modal */}
      {deleteTarget && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4"
          style={{ backdropFilter: "blur(8px)", backgroundColor: "rgba(44,24,16,0.5)" }}
          onClick={() => setDeleteTarget(null)}>
          <div className="bg-card rounded-2xl border border-border shadow-2xl w-full max-w-sm p-6"
            onClick={e => e.stopPropagation()}>
            <div className="w-14 h-14 bg-destructive/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <Trash2 className="w-7 h-7 text-destructive" />
            </div>
            <h3 className="text-center font-bold text-foreground text-lg mb-1" style={{ fontFamily: "'Playfair Display', serif" }}>
              Hapus Produk?
            </h3>
            <p className="text-center text-muted-foreground text-sm mb-1">
              Anda akan menghapus
            </p>
            <p className="text-center font-semibold text-foreground mb-5">"{deleteTarget.name}"</p>
            <p className="text-center text-xs text-muted-foreground bg-muted/60 rounded-xl px-4 py-2.5 mb-6">
              Tindakan ini tidak dapat dibatalkan.
            </p>
            <div className="flex gap-3">
              <Btn variant="outline" className="flex-1" onClick={() => setDeleteTarget(null)}>Batal</Btn>
              <Btn variant="danger" className="flex-1" onClick={confirmDelete}>
                <Trash2 className="w-4 h-4" /> Ya, Hapus
              </Btn>
            </div>
          </div>
        </div>
      )}

      {showForm && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <div className="bg-card rounded-2xl border border-border p-6 w-full max-w-lg shadow-xl">
            <div className="flex items-center justify-between mb-5">
              <h3 className="font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>{editing ? "Edit Produk" : "Tambah Produk Baru"}</h3>
              <button onClick={() => setShowForm(false)} className="p-2 rounded-lg hover:bg-muted cursor-pointer"><X className="w-4 h-4" /></button>
            </div>
            <div className="flex flex-col gap-4">
              <Input label="Nama Produk" value={nama} onChange={setNama} required />
              <div className="flex flex-col gap-1.5">
                <label className="text-sm font-medium text-foreground">Deskripsi<span className="text-destructive ml-1">*</span></label>
                <textarea value={deskripsi} onChange={e => setDeskripsi(e.target.value)} rows={3}
                  className="w-full px-4 py-2.5 rounded-lg border border-border bg-input-background text-sm resize-none focus:outline-none focus:ring-2 focus:ring-ring/40 focus:border-primary" />
              </div>
              <Input label="Harga per Bal (Rp)" type="number" value={harga} onChange={setHarga} placeholder="85000" required />
              <div className="flex flex-col gap-1.5">
                <label className="text-sm font-medium text-foreground">Kategori Penjualan</label>
                <div className="flex gap-4 mt-1">
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input type="radio" checked={salesChannel === "online"} onChange={() => setSalesChannel("online")} className="w-4 h-4 text-primary" />
                    <span className="text-sm">Online (Katalog)</span>
                  </label>
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input type="radio" checked={salesChannel === "offline"} onChange={() => setSalesChannel("offline")} className="w-4 h-4 text-primary" />
                    <span className="text-sm">Offline (Toko Fisik)</span>
                  </label>
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input type="radio" checked={salesChannel === "both"} onChange={() => setSalesChannel("both")} className="w-4 h-4 text-primary" />
                    <span className="text-sm">Keduanya</span>
                  </label>
                </div>
              </div>
              <div className="flex flex-col gap-1.5">
                <label className="text-sm font-medium text-foreground">Foto Produk</label>
                <input ref={fotoRef} type="file" accept="image/*" className="hidden"
                  onChange={e => { const f = e.target.files?.[0]; if (f) setFotoUrl(URL.createObjectURL(f)); }} />
                {fotoUrl ? (
                  <div className="relative rounded-xl overflow-hidden border border-border">
                    <img src={fotoUrl} alt="Preview" className="w-full h-40 object-cover" />
                    <button onClick={() => setFotoUrl("")}
                      className="absolute top-2 right-2 w-7 h-7 bg-black/50 rounded-full flex items-center justify-center text-white hover:bg-black/70 cursor-pointer transition-colors">
                      <X className="w-3.5 h-3.5" />
                    </button>
                    <button onClick={() => fotoRef.current?.click()}
                      className="absolute bottom-2 right-2 flex items-center gap-1.5 bg-black/50 text-white text-xs px-2.5 py-1.5 rounded-lg hover:bg-black/70 cursor-pointer transition-colors">
                      <Upload className="w-3 h-3" /> Ganti
                    </button>
                  </div>
                ) : (
                  <div onClick={() => fotoRef.current?.click()}
                    className="border-2 border-dashed border-border rounded-xl p-6 text-center cursor-pointer hover:border-primary/50 hover:bg-muted/50 transition-colors">
                    <Upload className="w-6 h-6 text-muted-foreground mx-auto mb-1" />
                    <p className="text-sm text-muted-foreground">Klik untuk upload foto</p>
                    <p className="text-xs text-muted-foreground mt-0.5">PNG, JPG maks 5MB</p>
                  </div>
                )}
              </div>
              <div className="flex gap-3 mt-1">
                <Btn variant="outline" className="flex-1" onClick={() => setShowForm(false)}>Batal</Btn>
                <Btn className="flex-1" onClick={save}>Simpan</Btn>
              </div>
            </div>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        {products.map(p => (
          <div key={p.id} className="bg-card border border-border rounded-2xl overflow-hidden hover:shadow-md transition-shadow">
            <div className="h-44 bg-muted overflow-hidden">
              <img src={p.image} alt={p.name} className="w-full h-full object-cover" />
            </div>
            <div className="p-4">
              <h3 className="font-semibold text-foreground mb-1" style={{ fontFamily: "'Playfair Display', serif" }}>{p.name}</h3>
              <p className="text-xs text-muted-foreground line-clamp-2 mb-2">{p.description}</p>
              <p className="text-primary font-bold mb-4">{fmt(p.pricePerBal)}<span className="text-xs font-normal text-muted-foreground">/bal</span></p>
              <div className="flex gap-2">
                <button onClick={() => openEdit(p)} className="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium border border-border rounded-lg hover:bg-muted transition-colors cursor-pointer">
                  <Edit className="w-3.5 h-3.5" /> Edit
                </button>
                <button onClick={() => setDeleteTarget(p)} className="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-destructive border border-destructive/20 rounded-lg hover:bg-destructive/5 transition-colors cursor-pointer">
                  <Trash2 className="w-3.5 h-3.5" /> Hapus
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

// ─── Page: Admin Stock ─────────────────────────────────────────────────────────
export function AdminStock({ stocks, setStocks }: { stocks: StockItem[]; setStocks: React.Dispatch<React.SetStateAction<StockItem[]>> }) {
  const [adjustments, setAdjustments] = useState<Record<number, string>>({});
  const [saved, setSaved] = useState<number | null>(null);

  const updateAdj = (id: number, val: string) => setAdjustments(prev => ({ ...prev, [id]: val }));

  const applyAdd = (id: number) => {
    const adj = parseInt(adjustments[id] || "0") || 0;
    if (adj <= 0) return;
    setStocks(prev => prev.map(s => s.id === id ? { ...s, stock: s.stock + adj } : s));
    setAdjustments(prev => ({ ...prev, [id]: "0" }));
    setSaved(id);
    setTimeout(() => setSaved(null), 2000);
  };

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <div>
          <h2 className="text-xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Stok Offline</h2>
          
        </div>
      </div>

      {stocks.some(s => s.stock < 50) && (
        <div className="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 flex items-start gap-3">
          <AlertTriangle className="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" />
          <div>
            <p className="font-semibold text-amber-800 text-sm">Peringatan Stok Rendah</p>
            <p className="text-sm text-amber-700 mt-0.5">
              {stocks.filter(s => s.stock < 50).map(s => s.name).join(", ")} — stok di bawah 50 bal.
            </p>
          </div>
        </div>
      )}

      <div className="bg-card border border-border rounded-2xl overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-border bg-muted/30">
                {["Produk", "Stok Saat Ini", "Aksi"].map(h => (
                  <th key={h} className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide">{h}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {stocks.map((s, i) => (
                <tr key={s.id} className={`border-b border-border last:border-0 ${i % 2 === 0 ? "" : "bg-muted/10"}`}>
                  <td className="px-4 py-4">
                    <div className="flex items-center gap-3">
                      <img src={s.image} alt={s.name} className="w-10 h-10 rounded-xl object-cover bg-muted flex-shrink-0" />
                      <div>
                        <p className="font-medium text-foreground">{s.name}</p>
                        <p className="text-xs text-muted-foreground">{s.category}</p>
                      </div>
                    </div>
                  </td>
                  <td className="px-4 py-4">
                    <span className="text-xl font-bold text-foreground">{s.stock}</span>
                    <span className="text-xs text-muted-foreground ml-1">bal</span>
                  </td>
                  <td className="px-4 py-4">
                    <div className="flex items-center gap-2">
                      <button onClick={() => { const v = Math.max(0, (parseInt(adjustments[s.id] || "0") || 0) - 1); updateAdj(s.id, v === 0 ? "" : String(v)); }}
                        className="w-7 h-7 rounded-lg border border-border flex items-center justify-center hover:bg-muted cursor-pointer">
                        <Minus className="w-3 h-3" />
                      </button>
                      <input type="number" value={adjustments[s.id] ?? ""} onChange={e => updateAdj(s.id, e.target.value)} placeholder="0" min={0}
                        className="w-16 text-center px-2 py-1.5 rounded-lg border border-border bg-input-background text-sm focus:outline-none focus:ring-2 focus:ring-ring/40" />
                      <button onClick={() => updateAdj(s.id, String((parseInt(adjustments[s.id] || "0") || 0) + 1))}
                        className="w-7 h-7 rounded-lg border border-border flex items-center justify-center hover:bg-muted cursor-pointer">
                        <Plus className="w-3 h-3" />
                      </button>
                      <button onClick={() => applyAdd(s.id)} title="Simpan"
                        className={`w-7 h-7 rounded-lg border flex items-center justify-center transition-colors cursor-pointer ${saved === s.id ? "bg-green-100 text-green-700 border-green-200" : "bg-primary/10 text-primary border-primary/20 hover:bg-primary/20"}`}>
                        <Check className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

// ─── App Root ─────────────────────────────────────────────────────────────────
