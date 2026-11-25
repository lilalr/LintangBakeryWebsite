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
type Page =
  | "home" | "auth" | "email-verify"
  | "catalog" | "product-detail" | "cart"
  | "checkout" | "checkout-success"
  | "buyer-dashboard" | "buyer-profile"
  | "admin-dashboard" | "admin-orders" | "admin-order-detail"
  | "admin-products" | "admin-stock"
  | "admin-transactions" | "admin-reports";

interface OfflineTransaction {
  id: string;
  date: string;
  buyerName: string;
  items: { product: Product; quantity: number; subtotal: number }[];
  total: number;
  paymentMethod: "cash" | "qris-tf";
  notes: string;
  status?: "PAID" | "VOID";
  voidReason?: string;
  voidDate?: string;
}

interface StockBatch {
  id: string;
  qty: number;
  expiry?: string;
}

interface StockItem extends Product {
  stock: number;
  batches?: StockBatch[];
}

interface StockMovement {
  id: string;
  ts: number;
  date: string;
  productId: number;
  productName: string;
  type: "Masuk" | "Keluar";
  qty: number;
  reason: string;
  ref: string;
  note: string;
  admin: string;
  balance: number;
  unitPrice: number;
}

interface MovementInput {
  productId: number;
  type: "Masuk" | "Keluar";
  qty: number;
  reason: string;
  ref?: string;
  note?: string;
  date?: string;
  expiry?: string;
}

type UserRole = "buyer" | "admin" | null;

interface Product {
  id: number;
  name: string;
  description: string;
  pricePerBal: number;
  image: string;
  category: string;
  salesChannel?: "online" | "offline" | "both";
  minStock?: number;
}

interface OrderItem {
  product: Product;
  quantity: number;
  subtotal: number;
}

interface Order {
  id: string;
  date: string;
  buyer: string;
  phone: string;
  address: string;
  city: string;
  province: string;
  items: OrderItem[];
  total: number;
  dp: number;
  remaining: number;
  paymentType: "full" | "dp";
  status: OrderStatus;
  paymentProof?: string;
  shippingCost: number;
}

type OrderStatus =
  | "Menunggu Konfirmasi"
  | "Pembayaran DP Disetujui"
  | "Sedang Diproduksi"
  | "Siap Dikirim"
  | "Sedang Dikirim"
  | "Sudah Sampai"
  | "Selesai/Lunas";

// ─── Data ─────────────────────────────────────────────────────────────────────
const PRODUCTS: Product[] = [
  {
    id: 1,
    name: "Nastar Nanas Premium",
    description: "Kue nastar dengan isian selai nanas asli pilihan. Tekstur lembut, rasa manis gurih yang khas. Cocok untuk reseller dan distributor yang menginginkan kualitas terbaik.",
    pricePerBal: 85000,
    image: "https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=600&h=500&fit=crop&auto=format",
    category: "Kue Lebaran",
  },
  {
    id: 2,
    name: "Kastengel Keju Edam",
    description: "Kastengel premium menggunakan keju Edam asli impor. Renyah di luar, lembut di dalam dengan aroma keju yang kuat dan menggugah selera.",
    pricePerBal: 95000,
    image: "https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=600&h=500&fit=crop&auto=format",
    category: "Kue Keju",
  },
  {
    id: 3,
    name: "Putri Salju Cokelat",
    description: "Putri salju klasik dengan varian cokelat. Balutan gula halus lembut berpadu cokelat premium menghasilkan cita rasa yang tak terlupakan.",
    pricePerBal: 80000,
    image: "https://images.unsplash.com/photo-1481391319762-47dff72954d9?w=600&h=500&fit=crop&auto=format",
    category: "Kue Salju",
  },
  {
    id: 4,
    name: "Lidah Kucing Vanilla",
    description: "Lidah kucing dengan aroma vanilla murni. Tipis, renyah, dan ringan — favorit semua kalangan. Dikemas rapi dalam bal berisi 12 toples.",
    pricePerBal: 75000,
    image: "https://images.unsplash.com/photo-1517427677506-ade074eb1432?w=600&h=500&fit=crop&auto=format",
    category: "Kue Kering Klasik",
  },
  {
    id: 5,
    name: "Semprit Meses Cokelat",
    description: "Semprit bertabur meses cokelat dengan aroma mentega yang wangi. Renyah dan manis, cocok untuk semua usia. Diproses higienis tanpa pengawet.",
    pricePerBal: 78000,
    image: "https://images.unsplash.com/photo-1550617931-e17a7b70dce2?w=600&h=500&fit=crop&auto=format",
    category: "Kue Kering Klasik",
  },
];

const PROVINCES: Record<string, string[]> = {
  "DI Yogyakarta": ["Kota Yogyakarta", "Sleman", "Bantul", "Kulonprogo", "Gunungkidul"],
  "Jawa Tengah": ["Kota Semarang", "Kota Solo", "Kota Magelang", "Cilacap", "Purwokerto", "Klaten", "Wonosobo"],
  "Jawa Barat": ["Kota Bandung", "Kota Bogor", "Bekasi", "Depok", "Cirebon", "Kab. Tasikmalaya", "Kota Tasikmalaya"],
  "Jawa Timur": ["Kota Surabaya", "Kota Malang", "Sidoarjo", "Gresik", "Kediri", "Jember"],
  "DKI Jakarta": ["Jakarta Pusat", "Jakarta Selatan", "Jakarta Utara", "Jakarta Barat", "Jakarta Timur"],
  "Banten": ["Kota Tangerang", "Kota Serang", "Lebak", "Pandeglang"],
  "Sumatera Utara": ["Kota Medan", "Deli Serdang", "Karo", "Serdang Bedagai"],
  "Sulawesi Selatan": ["Kota Makassar", "Gowa", "Maros", "Bone"],
};

const ORDER_STATUSES: OrderStatus[] = [
  "Menunggu Konfirmasi",
  "Pembayaran DP Disetujui",
  "Sedang Diproduksi",
  "Siap Dikirim",
  "Sedang Dikirim",
  "Sudah Sampai",
  "Selesai/Lunas",
];

const MOCK_ORDERS: Order[] = [
  {
    id: "LB-2024-001",
    date: "2024-12-10",
    buyer: "Toko Oleh-Oleh Makmur",
    phone: "089618512542",
    address: "Jl. Leuwi Anyar No. 18 RT 001/RW 003, Sukamanah",
    city: "Kab. Tasikmalaya",
    province: "Jawa Barat",
    items: [{ product: PRODUCTS[0], quantity: 150, subtotal: 150 * 85000 }],
    total: 150 * 85000,
    dp: Math.round(150 * 85000 * 0.65),
    remaining: Math.round(150 * 85000 * 0.35),
    paymentType: "dp",
    status: "Sedang Diproduksi",
    shippingCost: 150 * 10000,
  },
  {
    id: "LB-2024-002",
    date: "2024-12-11",
    buyer: "Distributor Snack Nusantara",
    phone: "087654321098",
    address: "Jl. Raya Soekarno-Hatta Blok C No. 12",
    city: "Kota Semarang",
    province: "Jawa Tengah",
    items: [
      { product: PRODUCTS[1], quantity: 100, subtotal: 100 * 95000 },
      { product: PRODUCTS[2], quantity: 80, subtotal: 80 * 80000 },
    ],
    total: 100 * 95000 + 80 * 80000,
    dp: 0,
    remaining: 0,
    paymentType: "full",
    status: "Menunggu Konfirmasi",
    shippingCost: (100 + 80) * 10000,
  },
  {
    id: "LB-2024-003",
    date: "2024-12-08",
    buyer: "Reseller Kue Jogja",
    phone: "089876543210",
    address: "Jl. Kaliurang Km. 8 No. 23",
    city: "Sleman",
    province: "DI Yogyakarta",
    items: [{ product: PRODUCTS[3], quantity: 250, subtotal: 250 * 75000 }],
    total: 250 * 75000,
    dp: Math.round(250 * 75000 * 0.65),
    remaining: Math.round(250 * 75000 * 0.35),
    paymentType: "dp",
    status: "Siap Dikirim",
    shippingCost: 250 * 10000,
  },
  {
    id: "LB-2024-004",
    date: "2024-12-05",
    buyer: "CV. Berkah Jaya Abadi",
    phone: "082233445566",
    address: "Jl. Pemuda No. 88",
    city: "Kota Bandung",
    province: "Jawa Barat",
    items: [
      { product: PRODUCTS[4], quantity: 200, subtotal: 200 * 78000 },
      { product: PRODUCTS[0], quantity: 120, subtotal: 120 * 85000 },
    ],
    total: 200 * 78000 + 120 * 85000,
    dp: 0,
    remaining: 0,
    paymentType: "full",
    status: "Selesai/Lunas",
    shippingCost: (200 + 120) * 10000,
  },
];

const MONTHLY_DATA = [
  { bulan: "Jan", pesanan: 18 },
  { bulan: "Feb", pesanan: 24 },
  { bulan: "Mar", pesanan: 31 },
  { bulan: "Apr", pesanan: 28 },
  { bulan: "Mei", pesanan: 42 },
  { bulan: "Jun", pesanan: 38 },
  { bulan: "Jul", pesanan: 45 },
  { bulan: "Agt", pesanan: 52 },
  { bulan: "Sep", pesanan: 47 },
  { bulan: "Okt", pesanan: 63 },
  { bulan: "Nov", pesanan: 78 },
  { bulan: "Des", pesanan: 89 },
];

// ─── Helpers ──────────────────────────────────────────────────────────────────
const fmt = (n: number) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(n);

const fmtDate = (d: string) => {
  if (!d) return "-";
  return d.split("-").reverse().join("/");
};

// ─── Manajemen Stok: konstanta & helper ───────────────────────────────────────
const ADMIN_NAME = "Admin";
const DEFAULT_MIN_STOCK = 20;
const EXPIRY_ALERT_DAYS = 14;
const LOSS_REASONS = ["Rusak", "Kedaluwarsa"];
const todayStr = () => new Date().toISOString().split("T")[0];
const STOCK_STATUS_STYLE: Record<string, string> = {
  Aman: "bg-green-50 text-green-700 border-green-200",
  Menipis: "bg-amber-50 text-amber-700 border-amber-200",
  Habis: "bg-red-50 text-red-700 border-red-200",
};

function stockStatus(s: StockItem): "Aman" | "Menipis" | "Habis" {
  const min = s.minStock ?? DEFAULT_MIN_STOCK;
  if (s.stock <= 0) return "Habis";
  if (s.stock <= min) return "Menipis";
  return "Aman";
}

function daysUntil(dateStr: string): number {
  return Math.ceil((new Date(dateStr).getTime() - new Date(todayStr()).getTime()) / 86400000);
}

function nearestExpiry(s: StockItem): string | undefined {
  const dates = (s.batches || []).filter(b => b.qty > 0 && b.expiry).map(b => b.expiry as string).sort();
  return dates[0];
}

function stockAlerts(stocks: StockItem[]) {
  const low = stocks.filter(s => stockStatus(s) !== "Aman");
  const expiring = stocks
    .map(s => ({ item: s, expiry: nearestExpiry(s) }))
    .filter((e): e is { item: StockItem; expiry: string } => !!e.expiry && daysUntil(e.expiry) <= EXPIRY_ALERT_DAYS)
    .map(e => ({ ...e, days: daysUntil(e.expiry) }));
  return { low, expiring };
}

// Menerapkan mutasi stok: update saldo, batch (FEFO untuk barang keluar), dan menulis baris kartu stok.
function applyMovements(stocks: StockItem[], inputs: MovementInput[]) {
  const cur: StockItem[] = stocks.map(s => ({ ...s, batches: (s.batches || []).map(b => ({ ...b })) }));
  const movements: StockMovement[] = [];
  const now = Date.now();
  inputs.forEach((m, idx) => {
    const s = cur.find(x => x.id === m.productId);
    if (!s) return;
    const batches = s.batches as StockBatch[];
    if (m.type === "Masuk") {
      s.stock += m.qty;
      if (m.reason !== "Pembatalan") batches.push({ id: `B-${now}-${idx}`, qty: m.qty, expiry: m.expiry || undefined });
    } else {
      s.stock = Math.max(0, s.stock - m.qty);
      let left = m.qty;
      batches.sort((a, b) => (a.expiry || "9999-12-31").localeCompare(b.expiry || "9999-12-31"));
      for (const b of batches) {
        if (left <= 0) break;
        const take = Math.min(b.qty, left);
        b.qty -= take;
        left -= take;
      }
      s.batches = batches.filter(b => b.qty > 0);
    }
    movements.push({
      id: `MV-${now}-${idx}`,
      ts: now * 100 + idx,
      date: m.date || todayStr(),
      productId: s.id,
      productName: s.name,
      type: m.type,
      qty: m.qty,
      reason: m.reason,
      ref: m.ref || "-",
      note: m.note || "",
      admin: ADMIN_NAME,
      balance: s.stock,
      unitPrice: s.pricePerBal,
    });
  });
  return { stocks: cur, movements };
}

function openingMovements(stocks: StockItem[]): StockMovement[] {
  const now = Date.now();
  return stocks.filter(s => s.stock > 0).map((s, i) => ({
    id: `MV-OPEN-${s.id}`,
    ts: now * 100 + i,
    date: todayStr(),
    productId: s.id,
    productName: s.name,
    type: "Masuk" as const,
    qty: s.stock,
    reason: "Saldo awal",
    ref: "SALDO-AWAL",
    note: "",
    admin: ADMIN_NAME,
    balance: s.stock,
    unitPrice: s.pricePerBal,
  }));
}

const statusColor: Record<OrderStatus, string> = {
  "Menunggu Konfirmasi": "bg-amber-100 text-amber-800 border-amber-200",
  "Pembayaran DP Disetujui": "bg-blue-100 text-blue-800 border-blue-200",
  "Sedang Diproduksi": "bg-purple-100 text-purple-800 border-purple-200",
  "Siap Dikirim": "bg-cyan-100 text-cyan-800 border-cyan-200",
  "Sedang Dikirim": "bg-orange-100 text-orange-800 border-orange-200",
  "Sudah Sampai": "bg-teal-100 text-teal-800 border-teal-200",
  "Selesai/Lunas": "bg-green-100 text-green-800 border-green-200",
};

// ─── Shared Components ────────────────────────────────────────────────────────
function Btn({
  children, onClick, variant = "primary", size = "md", className = "", disabled = false, type = "button"
}: {
  children: React.ReactNode; onClick?: () => void; variant?: "primary" | "secondary" | "outline" | "ghost" | "danger";
  size?: "sm" | "md" | "lg"; className?: string; disabled?: boolean; type?: "button" | "submit";
}) {
  const base = "inline-flex items-center justify-center gap-2 font-medium rounded-lg transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed";
  const sizes = { sm: "px-3 py-1.5 text-sm", md: "px-5 py-2.5 text-sm", lg: "px-7 py-3.5 text-base" };
  const variants = {
    primary: "bg-primary text-primary-foreground hover:bg-[#7a3b10] shadow-sm hover:shadow-md",
    secondary: "bg-secondary text-secondary-foreground hover:bg-[#ecd49a]",
    outline: "border border-primary text-primary hover:bg-primary/8",
    ghost: "text-primary hover:bg-primary/8",
    danger: "bg-destructive text-destructive-foreground hover:opacity-90",
  };
  return (
    <button type={type} onClick={onClick} disabled={disabled}
      className={`${base} ${sizes[size]} ${variants[variant]} ${className}`}>
      {children}
    </button>
  );
}

function StatusBadge({ status }: { status: OrderStatus }) {
  return (
    <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border ${statusColor[status]}`}>
      {status}
    </span>
  );
}

function Input({ label, type = "text", value, onChange, placeholder, required, error }: {
  label?: string; type?: string; value: string; onChange: (v: string) => void;
  placeholder?: string; required?: boolean; error?: string;
}) {
  return (
    <div className="flex flex-col gap-1.5">
      {label && <label className="text-sm font-medium text-foreground">{label}{required && <span className="text-destructive ml-1">*</span>}</label>}
      <input
        type={type} value={value} onChange={e => onChange(e.target.value)}
        placeholder={placeholder}
        className="w-full px-4 py-2.5 rounded-lg border border-border bg-input-background text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring/40 focus:border-primary transition-colors text-sm"
      />
      {error && <span className="text-xs text-destructive">{error}</span>}
    </div>
  );
}

function Select({ label, value, onChange, options, placeholder, required }: {
  label?: string; value: string; onChange: (v: string) => void;
  options: string[]; placeholder?: string; required?: boolean;
}) {
  return (
    <div className="flex flex-col gap-1.5">
      {label && <label className="text-sm font-medium text-foreground">{label}{required && <span className="text-destructive ml-1">*</span>}</label>}
      <div className="relative">
        <select
          value={value} onChange={e => onChange(e.target.value)}
          className="w-full px-4 py-2.5 pr-10 rounded-lg border border-border bg-input-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring/40 focus:border-primary appearance-none cursor-pointer text-sm"
        >
          {placeholder && <option value="">{placeholder}</option>}
          {options.map(o => <option key={o} value={o}>{o}</option>)}
        </select>
        <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
      </div>
    </div>
  );
}

// ─── Navbar ───────────────────────────────────────────────────────────────────
function Navbar({ page, setPage, cartCount, isLoggedIn, userRole, onLogout }: {
  page: Page; setPage: (p: Page) => void; cartCount: number;
  isLoggedIn: boolean; userRole: UserRole; onLogout: () => void;
}) {
  const [mobileOpen, setMobileOpen] = useState(false);

  const scrollTo = (id: string) => {
    setTimeout(() => {
      document.getElementById(id)?.scrollIntoView({ behavior: "smooth", block: "start" });
    }, 50);
  };

  const navItems = [
    { label: "Beranda", action: () => { setPage("home"); scrollTo("hero"); } },
    { label: "Produk", action: () => setPage("catalog") },
    { label: "Tentang Kami", action: () => { setPage("home"); scrollTo("tentang-kami"); } },
    { label: "Cara Pesan", action: () => { setPage("home"); scrollTo("cara-pesan"); } },
  ];

  return (
    <nav className="sticky top-0 z-50 bg-card/95 backdrop-blur-sm border-b border-border shadow-sm">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <button onClick={() => { setPage("home"); scrollTo("hero"); }} className="flex items-center gap-2.5 cursor-pointer">
          <div className="w-9 h-9 rounded-full bg-primary flex items-center justify-center">
            <span className="text-primary-foreground font-bold text-sm" style={{ fontFamily: "'Playfair Display', serif" }}>L</span>
          </div>
          <span className="font-bold text-lg text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>
            Lintang <span className="text-primary">Bakery</span>
          </span>
        </button>

        <div className="hidden md:flex items-center gap-6">
          {navItems.map(item => (
            <button key={item.label} onClick={item.action}
              className="text-sm font-medium transition-colors hover:text-primary cursor-pointer text-foreground/80">
              {item.label}
            </button>
          ))}
        </div>

        <div className="flex items-center gap-2">
          {isLoggedIn && userRole === "buyer" && (
            <button onClick={() => setPage("cart")} className="relative p-2 rounded-lg hover:bg-muted transition-colors cursor-pointer">
              <ShoppingCart className="w-5 h-5 text-foreground" />
              {cartCount > 0 && (
                <span className="absolute -top-1 -right-1 w-5 h-5 bg-primary text-primary-foreground text-[10px] font-bold rounded-full flex items-center justify-center">{cartCount}</span>
              )}
            </button>
          )}
          {isLoggedIn ? (
            <div className="flex items-center gap-1">
              <button onClick={() => setPage(userRole === "admin" ? "admin-dashboard" : "buyer-dashboard")}
                className="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-muted transition-colors cursor-pointer text-sm font-medium">
                <User className="w-4 h-4 text-primary" />
                <span className="hidden sm:inline text-foreground/80">{userRole === "admin" ? "Admin" : "Akun"}</span>
              </button>
              <button onClick={onLogout} className="p-2 rounded-lg hover:bg-muted transition-colors cursor-pointer" title="Keluar">
                <LogOut className="w-4 h-4 text-muted-foreground" />
              </button>
            </div>
          ) : (
            <Btn size="sm" onClick={() => setPage("auth")}>Masuk</Btn>
          )}
          <button onClick={() => setMobileOpen(!mobileOpen)} className="md:hidden p-2 rounded-lg hover:bg-muted cursor-pointer">
            {mobileOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
          </button>
        </div>
      </div>
      {mobileOpen && (
        <div className="md:hidden border-t border-border bg-card px-4 py-3 flex flex-col gap-2">
          {navItems.map(item => (
            <button key={item.label} onClick={() => { item.action(); setMobileOpen(false); }}
              className="text-left px-3 py-2 rounded-lg hover:bg-muted text-sm font-medium text-foreground/80 hover:text-primary transition-colors cursor-pointer">
              {item.label}
            </button>
          ))}
        </div>
      )}
    </nav>
  );
}

// ─── Footer ───────────────────────────────────────────────────────────────────
function Footer({ setPage }: { setPage: (p: Page) => void }) {
  return (
    <footer className="bg-[#2C1810] text-[#F5DEB3]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-10">
          <div>
            <div className="flex items-center gap-2.5 mb-4">
              <div className="w-9 h-9 rounded-full bg-primary flex items-center justify-center">
                <span className="text-white font-bold text-sm" style={{ fontFamily: "'Playfair Display', serif" }}>L</span>
              </div>
              <span className="font-bold text-xl text-white" style={{ fontFamily: "'Playfair Display', serif" }}>Lintang Bakery</span>
            </div>
            <p className="text-sm text-[#D2B48C] leading-relaxed">
              Produsen kue kering berkualitas untuk reseller dan distributor seluruh Indonesia.
            </p>
            <div className="flex gap-3 mt-4">
              {["FB", "IG", "WA"].map(s => (
                <div key={s} className="w-8 h-8 rounded-full bg-primary/30 flex items-center justify-center text-xs font-bold text-[#F5DEB3] cursor-pointer hover:bg-primary transition-colors">{s}</div>
              ))}
            </div>
          </div>
          <div>
            <h4 className="font-semibold text-white mb-4">Menu</h4>
            <div className="flex flex-col gap-2">
              {[["Beranda", "home"], ["Produk", "catalog"], ["Cara Pesan", "home"], ["Tentang Kami", "home"]].map(([label, target]) => (
                <button key={label} onClick={() => setPage(target as Page)}
                  className="text-left text-sm text-[#D2B48C] hover:text-white transition-colors cursor-pointer">{label}</button>
              ))}
            </div>
          </div>
          <div>
            <h4 className="font-semibold text-white mb-4">Kontak</h4>
            <div className="flex flex-col gap-3">
              <div className="flex items-start gap-3 text-sm text-[#D2B48C]">
                <MapPin className="w-4 h-4 mt-0.5 text-primary flex-shrink-0" />
                <a
                  href="https://maps.app.goo.gl/HAPajsu2GzPeA6GUA"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="hover:text-white transition-colors leading-relaxed"
                >
                  Jl. Leuwi Anyar No. 18 RT 001/RW 003, Sukamanah, Kec. Cipedes, Kab. Tasikmalaya, Jawa Barat 46131
                </a>
              </div>
              <div className="flex items-center gap-3 text-sm text-[#D2B48C]">
                <Phone className="w-4 h-4 text-primary flex-shrink-0" />
                <a href="tel:+6289618512542" className="hover:text-white transition-colors">
                  +62 896-1851-2542
                </a>
              </div>
              <div className="flex items-center gap-3 text-sm text-[#D2B48C]">
                <Mail className="w-4 h-4 text-primary flex-shrink-0" />
                <span className="italic">— segera diinformasikan —</span>
              </div>
            </div>
          </div>
        </div>
        <div className="border-t border-[#4A2C1A] mt-10 pt-6 text-center text-xs text-[#8B6F5E]">
          © 2024 Lintang Bakery. Semua hak dilindungi.
        </div>
      </div>
    </footer>
  );
}

// ─── Page: Home ───────────────────────────────────────────────────────────────
function HomePage({ products, setPage, setSelectedProduct, addToCart, isLoggedIn }: { products: Product[];
  setPage: (p: Page) => void;
  setSelectedProduct: (p: Product) => void;
  addToCart: (p: Product, qty: number) => void;
  isLoggedIn: boolean;
}) {
  const [cartModalProduct, setCartModalProduct] = useState<Product | null>(null);
  return (
    <div>
      {/* Hero */}
      <section id="hero" className="relative min-h-[88vh] flex items-center overflow-hidden">
        <div className="absolute inset-0">
          <img
            src="https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=1600&h=900&fit=crop&auto=format"
            alt="Lintang Bakery kue kering"
            className="w-full h-full object-cover"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-[#2C1810]/90 via-[#2C1810]/60 to-transparent" />
        </div>
        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 py-24 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div>
            <div className="inline-flex items-center gap-2 bg-primary/20 border border-primary/30 text-[#F5DEB3] text-sm px-4 py-1.5 rounded-full mb-6">
              <Star className="w-3.5 h-3.5 fill-current" />
              Supplier Kue Kering #1 di Yogyakarta
            </div>
            <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6"
              style={{ fontFamily: "'Playfair Display', serif" }}>
              Pesan Kue Kering<br />
              <span className="text-[#F5DEB3]">Berkualitas</span><br />
              Langsung dari<br />Produsen
            </h1>
            <p className="text-[#D2B48C] text-lg mb-8 leading-relaxed max-w-md">
              Grosir kue kering premium untuk reseller & distributor. Harga kompetitif, produksi higienis, pengiriman ke seluruh Indonesia.
            </p>
            <div className="flex flex-wrap gap-4">
              <Btn size="lg" onClick={() => setPage("catalog")}>
                Pesan Sekarang <ArrowRight className="w-4 h-4" />
              </Btn>
              <Btn size="lg" variant="outline" onClick={() => setPage("catalog")}
                className="border-white/40 text-white hover:bg-white/10">
                Lihat Produk
              </Btn>
            </div>
            <div className="flex items-center gap-6 mt-10">
              {[["500+", "Reseller"], ["50rb+", "Bal Terjual"], ["5 Tahun", "Pengalaman"]].map(([val, label]) => (
                <div key={label}>
                  <div className="text-xl font-bold text-white" style={{ fontFamily: "'Playfair Display', serif" }}>{val}</div>
                  <div className="text-xs text-[#D2B48C]">{label}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Tentang Kami */}
      <section id="tentang-kami" className="py-20 bg-background">
        <div className="max-w-7xl mx-auto px-4 sm:px-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">
            <div className="relative">
              <div className="rounded-3xl overflow-hidden aspect-[4/3] bg-muted">
                <img
                  src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800&h=600&fit=crop&auto=format"
                  alt="Dapur produksi Lintang Bakery"
                  className="w-full h-full object-cover"
                />
              </div>
              <div className="absolute -bottom-5 -right-5 hidden lg:block">
                <div className="bg-card border border-border rounded-2xl shadow-lg px-6 py-4 flex items-center gap-4">
                  <div className="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                    <Star className="w-6 h-6 text-primary fill-primary" />
                  </div>
                  <div>
                    <p className="font-bold text-foreground text-lg leading-none">5 Tahun</p>
                    <p className="text-xs text-muted-foreground mt-0.5">Melayani reseller & distributor</p>
                  </div>
                </div>
              </div>
            </div>
            <div>
              <p className="text-primary text-sm font-medium tracking-widest uppercase mb-2">Tentang Kami</p>
              <h2 className="text-3xl sm:text-4xl font-bold text-foreground leading-snug mb-5" style={{ fontFamily: "'Playfair Display', serif" }}>
                Dari Dapur Rumahan<br />ke Seluruh Indonesia
              </h2>
              <p className="text-muted-foreground leading-relaxed mb-4">
                Lintang Bakery adalah usaha rumahan (UMKM) yang berdiri sejak 2019 di Tasikmalaya, Jawa Barat. Berawal dari cinta terhadap kue kering tradisional, kami tumbuh menjadi produsen grosir yang kini melayani ratusan reseller dan distributor dari Sabang sampai Merauke.
              </p>
              <p className="text-muted-foreground leading-relaxed mb-8">
                Setiap produk kami dibuat dari bahan baku pilihan, diproses secara higienis, dan tanpa pengawet berbahaya. Kami percaya bahwa kualitas yang terjaga adalah kunci kepercayaan jangka panjang bersama mitra bisnis kami.
              </p>
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                {[
                  { icon: <Shield className="w-5 h-5" />, label: "Bersertifikat Halal", sub: "MUI Jawa Barat" },
                  { icon: <Package className="w-5 h-5" />, label: "Tanpa Pengawet", sub: "Bahan alami pilihan" },
                  { icon: <Layers className="w-5 h-5" />, label: "Produksi Higienis", sub: "Standar PIRT" },
                ].map(item => (
                  <div key={item.label} className="flex items-start gap-3 bg-secondary/30 rounded-2xl p-4 border border-secondary">
                    <div className="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
                      {item.icon}
                    </div>
                    <div>
                      <p className="font-semibold text-foreground text-sm">{item.label}</p>
                      <p className="text-xs text-muted-foreground mt-0.5">{item.sub}</p>
                    </div>
                  </div>
                ))}
              </div>
              <div className="flex items-center gap-4">
                <div className="flex -space-x-2">
                  {[
                    "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=40&h=40&fit=crop",
                    "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop",
                    "https://images.unsplash.com/photo-1554151228-14d9def656e4?w=40&h=40&fit=crop",
                  ].map((src, i) => (
                    <img key={i} src={src} alt="reseller" className="w-9 h-9 rounded-full border-2 border-card object-cover bg-muted" />
                  ))}
                </div>
                <p className="text-sm text-muted-foreground">
                  Dipercaya oleh <span className="font-semibold text-foreground">500+ reseller</span> aktif di seluruh Indonesia
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Products */}
      <section id="produk" className="py-20 bg-background">
        <div className="max-w-7xl mx-auto px-4 sm:px-6">
          <div className="text-center mb-12">
            <p className="text-primary text-sm font-medium tracking-widest uppercase mb-2">Produk Unggulan</p>
            <h2 className="text-3xl sm:text-4xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>
              Kue Kering Pilihan
            </h2>
            <p className="text-muted-foreground mt-3 max-w-xl mx-auto">
              Diproduksi dengan bahan baku pilihan dan proses higienis bersertifikat halal.
            </p>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5">
            {PRODUCTS.map(p => (
              <div key={p.id} className="bg-card rounded-2xl overflow-hidden border border-border hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                <div className="relative h-48 bg-muted overflow-hidden">
                  <img src={p.image} alt={p.name} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                  <div className="absolute top-3 left-3">
                    <span className="bg-card/90 backdrop-blur-sm text-xs px-2.5 py-1 rounded-full text-muted-foreground border border-border">{p.category}</span>
                  </div>
                </div>
                <div className="p-4">
                  <h3 className="font-semibold text-foreground text-sm leading-snug mb-1" style={{ fontFamily: "'Playfair Display', serif" }}>{p.name}</h3>
                  <p className="text-primary font-bold text-base mb-3">{fmt(p.pricePerBal)}<span className="text-xs font-normal text-muted-foreground">/bal</span></p>
                  <div className="flex gap-2">
                    <Btn size="sm" className="flex-1" onClick={() => { setSelectedProduct(p); setPage("product-detail"); }}>
                      Pesan Sekarang
                    </Btn>
                    <button
                      onClick={() => {
                        if (!isLoggedIn) { setPage("auth"); return; }
                        setCartModalProduct(p);
                      }}
                      className="flex items-center justify-center w-9 h-9 rounded-lg bg-primary/10 hover:bg-primary hover:text-white text-primary transition-all duration-200 cursor-pointer flex-shrink-0"
                      title="Tambah ke Keranjang"
                    >
                      <ShoppingCart className="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
          <div className="text-center mt-8">
            <Btn onClick={() => setPage("catalog")}>Lihat Semua Produk <ArrowRight className="w-4 h-4" /></Btn>
          </div>
        </div>
      </section>

      {/* Why section */}
      <section id="kenapa" className="py-20 bg-secondary/30">
        <div className="max-w-7xl mx-auto px-4 sm:px-6">
          <div className="text-center mb-12">
            <p className="text-primary text-sm font-medium tracking-widest uppercase mb-2">Keunggulan Kami</p>
            <h2 className="text-3xl sm:text-4xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>
              Kenapa Pesan di Sini?
            </h2>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {[
              { icon: <Truck className="w-8 h-8" />, title: "Pengiriman Langsung", desc: "Dikirim langsung dari dapur produksi ke tangan Anda. Kami bekerja sama dengan ekspedisi terpercaya untuk jaminan keamanan produk." },
              { icon: <RefreshCw className="w-8 h-8" />, title: "Lacak Pesanan", desc: "Pantau status pesanan Anda secara real-time mulai dari konfirmasi, produksi, hingga barang tiba di tujuan." },
              { icon: <Shield className="w-8 h-8" />, title: "Terpercaya & Halal", desc: "Produksi bersertifikat halal MUI, higienis, dan tanpa pengawet berbahaya. Kepuasan pelanggan adalah prioritas utama kami." },
            ].map(item => (
              <div key={item.title} className="bg-card rounded-2xl p-8 border border-border text-center hover:shadow-md transition-shadow">
                <div className="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary mx-auto mb-5">
                  {item.icon}
                </div>
                <h3 className="font-semibold text-foreground text-lg mb-3" style={{ fontFamily: "'Playfair Display', serif" }}>{item.title}</h3>
                <p className="text-muted-foreground text-sm leading-relaxed">{item.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Cara Pesan */}
      <section id="cara-pesan" className="py-20 bg-[#2C1810]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6">
          <div className="text-center mb-12">
            <p className="text-[#F5DEB3] text-sm font-medium tracking-widest uppercase mb-2">Panduan</p>
            <h2 className="text-3xl sm:text-4xl font-bold text-white" style={{ fontFamily: "'Playfair Display', serif" }}>
              Cara Pesan
            </h2>
            <p className="text-[#D2B48C] mt-3 max-w-xl mx-auto">Proses pemesanan grosir yang mudah, cepat, dan transparan.</p>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {[
              { step: "01", icon: <User className="w-6 h-6" />, title: "Daftar Akun", desc: "Buat akun reseller/distributor dengan mengisi data diri dan alamat pengiriman Anda." },
              { step: "02", icon: <ShoppingBag className="w-6 h-6" />, title: "Pilih Produk", desc: "Pilih produk dan tentukan jumlah bal yang ingin dipesan. Minimum 100 bal per produk." },
              { step: "03", icon: <Copy className="w-6 h-6" />, title: "Lakukan Pembayaran", desc: "Transfer via BRI, BCA, atau QRIS. Upload bukti pembayaran agar pesanan segera diproses." },
              { step: "04", icon: <Truck className="w-6 h-6" />, title: "Barang Dikirim", desc: "Pantau status produksi dan pengiriman secara real-time melalui dashboard Anda." },
            ].map(item => (
              <div key={item.step} className="relative">
                <div className="bg-white/5 border border-white/10 rounded-2xl p-6 hover:bg-white/8 transition-colors h-full">
                  <div className="flex items-center justify-between mb-5">
                    <div className="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center text-[#F5DEB3]">
                      {item.icon}
                    </div>
                    <span className="text-4xl font-bold text-white/10 select-none" style={{ fontFamily: "'Playfair Display', serif" }}>{item.step}</span>
                  </div>
                  <h3 className="font-semibold text-white mb-2" style={{ fontFamily: "'Playfair Display', serif" }}>{item.title}</h3>
                  <p className="text-[#D2B48C] text-sm leading-relaxed">{item.desc}</p>
                </div>
                {item.step !== "04" && (
                  <div className="hidden lg:flex absolute top-1/2 -right-3 -translate-y-1/2 z-10 w-6 h-6 rounded-full bg-primary items-center justify-center">
                    <ChevronRight className="w-3.5 h-3.5 text-white" />
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Banner */}
      <section className="py-16 bg-primary">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 text-center">
          <h2 className="text-3xl sm:text-4xl font-bold text-primary-foreground mb-4" style={{ fontFamily: "'Playfair Display', serif" }}>
            Tertarik Pesan Kue Kering Kami?
          </h2>
          <p className="text-[#F5DEB3] mb-8 text-lg">Buat akun sekarang dan pesan langsung dari produsen — mudah, cepat, dan terpercaya.</p>
          <div className="flex flex-wrap items-center justify-center gap-4">
            <Btn size="lg" variant="secondary" onClick={() => setPage("auth")}>
              Daftar Sekarang <ArrowRight className="w-4 h-4" />
            </Btn>
            <Btn size="lg" onClick={() => setPage("catalog")}
              className="border-white/40 text-white hover:bg-white/10 bg-transparent border">
              Lihat Produk
            </Btn>
          </div>
        </div>
      </section>

      {cartModalProduct && (
        <AddToCartModal
          product={cartModalProduct}
          onClose={() => setCartModalProduct(null)}
          onConfirm={(qty) => {
            addToCart(cartModalProduct, qty);
            toast.success(`${cartModalProduct.name} ditambahkan ke keranjang`, { description: `${qty} bal` });
            setCartModalProduct(null);
          }}
        />
      )}
    </div>
  );
}

// ─── Page: Auth ───────────────────────────────────────────────────────────────
function AuthPage({ setPage, setIsLoggedIn, setUserRole }: {
  setPage: (p: Page) => void;
  setIsLoggedIn: (v: boolean) => void;
  setUserRole: (r: UserRole) => void;
}) {
  const [tab, setTab] = useState<"masuk" | "daftar">("masuk");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [nama, setNama] = useState("");
  const [telepon, setTelepon] = useState("");
  const [alamat, setAlamat] = useState("");
  const [konfirmasi, setKonfirmasi] = useState("");
  const [provinsi, setProvinsi] = useState("");
  const [kota, setKota] = useState("");

  const handleLogin = () => {
    if (email === "admin@lintangbakery.id") {
      setIsLoggedIn(true); setUserRole("admin"); setPage("admin-dashboard");
    } else {
      setIsLoggedIn(true); setUserRole("buyer"); setPage("home");
    }
  };

  const handleRegister = () => {
    setPage("email-verify");
  };

  const kotaOptions = provinsi ? PROVINCES[provinsi] || [] : [];

  return (
    <div className="min-h-screen bg-muted flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        <button onClick={() => setPage("home")}
          className="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors cursor-pointer mb-6">
          <ChevronRight className="w-4 h-4 rotate-180" /> Kembali ke Beranda
        </button>
        <div className="text-center mb-8">
          <div className="w-14 h-14 rounded-full bg-primary flex items-center justify-center mx-auto mb-3">
            <span className="text-white font-bold text-xl" style={{ fontFamily: "'Playfair Display', serif" }}>L</span>
          </div>
          <h1 className="text-2xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Lintang Bakery</h1>
          <p className="text-muted-foreground text-sm mt-1">Portal Reseller & Distributor</p>
        </div>

        <div className="bg-card rounded-2xl border border-border shadow-sm overflow-hidden">
          <div className="flex border-b border-border">
            {(["masuk", "daftar"] as const).map(t => (
              <button key={t} onClick={() => setTab(t)}
                className={`flex-1 py-4 text-sm font-medium transition-colors cursor-pointer capitalize ${tab === t ? "text-primary border-b-2 border-primary bg-primary/5" : "text-muted-foreground hover:text-foreground"}`}>
                {t === "masuk" ? "Masuk" : "Daftar"}
              </button>
            ))}
          </div>

          <div className="p-6">
            {tab === "masuk" ? (
              <div className="flex flex-col gap-4">
                <Input label="Email" type="email" value={email} onChange={setEmail} placeholder="contoh@email.com" />
                <Input label="Password" type="password" value={password} onChange={setPassword} placeholder="••••••••" />
                <div className="text-right">
                  <button className="text-xs text-primary hover:underline cursor-pointer">Lupa password?</button>
                </div>
                <Btn className="w-full mt-1" size="lg" onClick={handleLogin}>Masuk</Btn>
                <div className="bg-muted/60 rounded-xl p-3 border border-border">
                  <p className="text-xs text-muted-foreground font-medium mb-2">Demo akun:</p>
                  <div className="flex flex-col gap-1">
                    <button onClick={() => { setEmail("buyer@test.com"); setPassword("password"); }}
                      className="text-xs text-left text-primary hover:underline cursor-pointer">
                      Buyer: buyer@test.com / password
                    </button>
                    <button onClick={() => { setEmail("admin@lintangbakery.id"); setPassword("admin123"); }}
                      className="text-xs text-left text-primary hover:underline cursor-pointer">
                      Admin: admin@lintangbakery.id / admin123
                    </button>
                  </div>
                </div>
              </div>
            ) : (
              <div className="flex flex-col gap-4">
                {/* Identitas diri */}
                <Input label="Nama Lengkap" value={nama} onChange={setNama} placeholder="Nama toko atau penanggung jawab" required />
                <Input label="Email" type="email" value={email} onChange={setEmail} placeholder="contoh@email.com" required />
                <Input label="No. Telepon / WhatsApp" type="tel" value={telepon} onChange={setTelepon} placeholder="08xxxxxxxxxx" required />
                {/* Alamat */}
                <Input label="Alamat Lengkap" value={alamat} onChange={setAlamat} placeholder="Nama jalan, nomor, RT/RW" required />
                <div className="grid grid-cols-2 gap-3">
                  <Select label="Provinsi" value={provinsi} onChange={v => { setProvinsi(v); setKota(""); }}
                    options={Object.keys(PROVINCES)} placeholder="— Provinsi —" required />
                  <Select label="Kota / Kabupaten" value={kota} onChange={setKota}
                    options={kotaOptions} placeholder={provinsi ? "— Kota —" : "— Pilih Provinsi —"}
                    required />
                </div>
                {/* Keamanan akun */}
                <Input label="Password" type="password" value={password} onChange={setPassword} placeholder="Min. 8 karakter" required />
                <Input label="Konfirmasi Password" type="password" value={konfirmasi} onChange={setKonfirmasi} placeholder="Ulangi password" required />
                <Btn className="w-full mt-1" size="lg" onClick={handleRegister}>Daftar Sekarang</Btn>
                <p className="text-xs text-center text-muted-foreground">
                  Dengan mendaftar, Anda menyetujui <span className="text-primary cursor-pointer hover:underline">Syarat & Ketentuan</span> kami.
                </p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Page: Email Verify ───────────────────────────────────────────────────────
function EmailVerifyPage({ setPage, setIsLoggedIn, setUserRole }: {
  setPage: (p: Page) => void;
  setIsLoggedIn: (v: boolean) => void;
  setUserRole: (r: UserRole) => void;
}) {
  return (
    <div className="min-h-screen bg-muted flex items-center justify-center p-4">
      <div className="bg-card rounded-2xl border border-border p-10 text-center max-w-md w-full shadow-sm">
        <div className="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
          <Mail className="w-10 h-10 text-primary" />
        </div>
        <h2 className="text-2xl font-bold text-foreground mb-3" style={{ fontFamily: "'Playfair Display', serif" }}>Verifikasi Email Anda</h2>
        <p className="text-muted-foreground text-sm leading-relaxed mb-6">
          Kami telah mengirimkan tautan verifikasi ke email Anda. Silakan cek kotak masuk atau folder spam.
        </p>
        <div className="bg-secondary/40 rounded-xl p-4 mb-6 text-sm text-secondary-foreground">
          Setelah verifikasi, akun Anda akan langsung aktif dan siap digunakan.
        </div>
        <Btn className="w-full" onClick={() => { setIsLoggedIn(true); setUserRole("buyer"); setPage("home"); }}>
          Lanjutkan ke Beranda
        </Btn>
        <button className="mt-3 text-sm text-muted-foreground hover:text-primary cursor-pointer">Kirim ulang email</button>
      </div>
    </div>
  );
}

// ─── Page: Catalog ────────────────────────────────────────────────────────────
function CatalogPage({ products, setPage, setSelectedProduct, addToCart, isLoggedIn }: { products: Product[];
  setPage: (p: Page) => void;
  setSelectedProduct: (p: Product) => void;
  addToCart: (p: Product, qty: number) => void;
  isLoggedIn: boolean;
}) {
  const [search, setSearch] = useState("");
  const [cartModalProduct, setCartModalProduct] = useState<Product | null>(null);
  const filtered = PRODUCTS.filter(p =>
    p.name.toLowerCase().includes(search.toLowerCase()) ||
    p.category.toLowerCase().includes(search.toLowerCase())
  );
  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 py-12">
      <div className="mb-8">
        <p className="text-primary text-sm font-medium tracking-widest uppercase mb-1">Katalog</p>
        <h1 className="text-3xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Produk Kami</h1>
        <p className="text-muted-foreground mt-2">Kue kering grosir berkualitas untuk reseller dan distributor</p>
      </div>
      <div className="relative max-w-sm mb-8">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
        <input
          value={search} onChange={e => setSearch(e.target.value)}
          placeholder="Cari produk..."
          className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-border bg-input-background text-sm focus:outline-none focus:ring-2 focus:ring-ring/40 focus:border-primary"
        />
      </div>
      <div className="bg-secondary/30 border border-secondary rounded-2xl p-4 mb-8 flex flex-wrap items-center gap-4">
        <div className="flex items-center gap-3">
          <AlertTriangle className="w-5 h-5 text-accent flex-shrink-0" />
          <p className="text-sm text-foreground">Minimum order <strong>100 bal per produk</strong></p>
        </div>
        <div className="h-4 w-px bg-border hidden sm:block" />
        <div className="flex items-center gap-2 text-sm text-muted-foreground">
          <span className="inline-flex items-center gap-1.5 bg-card border border-border rounded-full px-3 py-1 text-xs font-medium text-foreground">
            Bayar Penuh
          </span>
          <span className="text-muted-foreground">atau</span>
          <span className="inline-flex items-center gap-1.5 bg-accent/10 border border-accent/20 rounded-full px-3 py-1 text-xs font-medium text-accent">
            DP 65%
          </span>
          <span className="text-muted-foreground text-xs">· sisa 35% cash saat barang sampai</span>
        </div>
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        {filtered.map(p => (
          <div key={p.id} className="bg-card rounded-2xl overflow-hidden border border-border hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
            <div className="relative h-52 bg-muted overflow-hidden">
              <img src={p.image} alt={p.name} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
              <div className="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent" />
              <div className="absolute top-3 left-3">
                <span className="bg-card/90 backdrop-blur-sm text-xs px-2.5 py-1 rounded-full text-muted-foreground border border-border">{p.category}</span>
              </div>
            </div>
            <div className="p-5">
              <h3 className="font-semibold text-foreground mb-1" style={{ fontFamily: "'Playfair Display', serif" }}>{p.name}</h3>
              <p className="text-xs text-muted-foreground mb-3 line-clamp-2">{p.description}</p>
              <div className="flex items-center justify-between mb-3">
                <div>
                  <span className="text-primary font-bold text-lg">{fmt(p.pricePerBal)}</span>
                  <span className="text-xs text-muted-foreground">/bal</span>
                </div>
              </div>
              <div className="flex gap-2">
                <Btn size="sm" className="flex-1" onClick={() => { setSelectedProduct(p); setPage("product-detail"); }}>
                  Pesan Sekarang
                </Btn>
                <button
                  onClick={() => {
                    if (!isLoggedIn) { setPage("auth"); return; }
                    setCartModalProduct(p);
                  }}
                  className="flex items-center justify-center w-9 h-9 rounded-lg bg-primary/10 hover:bg-primary hover:text-white text-primary transition-all duration-200 cursor-pointer flex-shrink-0"
                  title="Tambah ke Keranjang"
                >
                  <ShoppingCart className="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>

      {cartModalProduct && (
        <AddToCartModal
          product={cartModalProduct}
          onClose={() => setCartModalProduct(null)}
          onConfirm={(qty) => {
            addToCart(cartModalProduct, qty);
            toast.success(`${cartModalProduct.name} ditambahkan ke keranjang`, { description: `${qty} bal` });
            setCartModalProduct(null);
          }}
        />
      )}
    </div>
  );
}

// ─── Page: Product Detail ─────────────────────────────────────────────────────
function ProductDetailPage({ product, setPage, addToCart, isLoggedIn, setCheckoutCart }: {
  product: Product | null; setPage: (p: Page) => void;
  addToCart: (p: Product, qty: number) => void; isLoggedIn: boolean;
  setCheckoutCart: (items: { product: Product; quantity: number }[]) => void;
}) {
  const [qty, setQty] = useState(100);
  if (!product) return null;

  const subtotal = product.pricePerBal * qty;
  const dp = Math.round(subtotal * 0.65);

  return (
    <div className="max-w-6xl mx-auto px-4 sm:px-6 py-12">
      <button onClick={() => setPage("catalog")} className="flex items-center gap-2 text-muted-foreground hover:text-foreground text-sm mb-8 cursor-pointer transition-colors">
        <ChevronRight className="w-4 h-4 rotate-180" /> Kembali ke Katalog
      </button>
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <div>
          <div className="rounded-2xl overflow-hidden aspect-square bg-muted">
            <img src={product.image} alt={product.name} className="w-full h-full object-cover" />
          </div>
        </div>
        <div>
          <span className="inline-block bg-secondary text-secondary-foreground text-xs px-3 py-1 rounded-full mb-3">{product.category}</span>
          <h1 className="text-3xl font-bold text-foreground mb-2" style={{ fontFamily: "'Playfair Display', serif" }}>{product.name}</h1>
          <div className="text-3xl font-bold text-primary mb-1">{fmt(product.pricePerBal)}<span className="text-base font-normal text-muted-foreground">/bal</span></div>
          <p className="text-muted-foreground text-sm leading-relaxed mb-6">{product.description}</p>

          {/* Info Box */}
          <div className="bg-secondary/40 border border-secondary rounded-xl p-4 mb-6">
            <p className="font-semibold text-foreground text-sm mb-2">Ketentuan Pembayaran:</p>
            <div className="flex flex-col gap-1.5">
              <div className="flex items-center gap-2 text-sm">
                <div className="w-1.5 h-1.5 rounded-full bg-primary flex-shrink-0" />
                <span className="text-foreground"><strong>Bayar Penuh</strong> — lunas di muka</span>
              </div>
              <div className="flex items-center gap-2 text-sm">
                <div className="w-1.5 h-1.5 rounded-full bg-accent flex-shrink-0" />
                <span className="text-foreground"><strong>DP 65%</strong> — bayar 65% sekarang, sisa 35% cash saat barang sampai</span>
              </div>
            </div>
          </div>

          {/* Quantity */}
          <div className="mb-6">
            <label className="text-sm font-medium text-foreground block mb-2">Jumlah (bal) — min. 100 bal</label>
            <div className="flex items-center gap-3">
              <button onClick={() => setQty(q => Math.max(100, q - 10))}
                className="w-10 h-10 rounded-xl border border-border bg-card hover:bg-muted flex items-center justify-center cursor-pointer transition-colors">
                <Minus className="w-4 h-4" />
              </button>
              <input
                type="number" value={qty}
                onChange={e => setQty(Math.max(100, parseInt(e.target.value) || 100))}
                min={100}
                className="w-24 text-center px-3 py-2 rounded-xl border border-border bg-input-background text-foreground font-medium focus:outline-none focus:ring-2 focus:ring-ring/40 focus:border-primary"
              />
              <button onClick={() => setQty(q => q + 10)}
                className="w-10 h-10 rounded-xl border border-border bg-card hover:bg-muted flex items-center justify-center cursor-pointer transition-colors">
                <Plus className="w-4 h-4" />
              </button>
              <span className="text-sm text-muted-foreground">bal</span>
            </div>
          </div>

          {/* Summary */}
          <div className="bg-card border border-border rounded-xl p-4 mb-6">
            <div className="flex justify-between text-sm mb-2">
              <span className="text-muted-foreground">Subtotal ({qty} bal)</span>
              <span className="font-medium">{fmt(subtotal)}</span>
            </div>
            <div className="border-t border-border pt-2 mt-2">
              <p className="text-xs text-muted-foreground mb-1.5">Jika pilih DP 65%:</p>
              <div className="flex justify-between text-sm mb-1">
                <span className="text-muted-foreground">DP 65% (bayar sekarang)</span>
                <span className="font-semibold text-accent">{fmt(dp)}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Sisa 35% (saat barang sampai)</span>
                <span className="font-medium text-muted-foreground">{fmt(subtotal - dp)}</span>
              </div>
            </div>
          </div>

          <div className="flex flex-col gap-3">
            <div className="flex gap-3">
              <Btn size="lg" variant="outline" className="flex-1" onClick={() => {
                if (!isLoggedIn) { setPage("auth"); return; }
                addToCart(product, qty);
                toast.success(`${product.name} ditambahkan ke keranjang`, { description: `${qty} bal` });
              }}>
                <ShoppingCart className="w-4 h-4" /> Tambah ke Keranjang
              </Btn>
              <Btn size="lg" className="flex-1" onClick={() => {
                if (!isLoggedIn) { setPage("auth"); return; }
                addToCart(product, qty);
                setCheckoutCart([{ product, quantity: qty }]);
                setPage("checkout");
              }}>
                Pesan Sekarang <ArrowRight className="w-4 h-4" />
              </Btn>
            </div>
            {!isLoggedIn && <p className="text-xs text-center text-muted-foreground">Anda perlu masuk terlebih dahulu untuk memesan</p>}
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Add To Cart Modal ───────────────────────────────────────────────────────
function AddToCartModal({ product, onClose, onConfirm }: {
  product: Product;
  onClose: () => void;
  onConfirm: (qty: number) => void;
}) {
  const [qty, setQty] = useState(100);
  const subtotal = product.pricePerBal * qty;
  const dp = Math.round(subtotal * 0.65);

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center p-4"
      style={{ backdropFilter: "blur(8px)", backgroundColor: "rgba(44,24,16,0.5)" }}
      onClick={onClose}
    >
      <div
        className="bg-card rounded-2xl border border-border shadow-2xl w-full max-w-sm p-6"
        onClick={e => e.stopPropagation()}
      >
        <div className="flex items-center justify-between mb-5">
          <h3 className="font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Tambah ke Keranjang</h3>
          <button onClick={onClose} className="p-1.5 rounded-lg hover:bg-muted cursor-pointer transition-colors">
            <X className="w-4 h-4 text-muted-foreground" />
          </button>
        </div>

        <div className="flex items-center gap-3 bg-muted/50 rounded-xl p-3 mb-5">
          <img src={product.image} alt={product.name} className="w-14 h-14 rounded-xl object-cover bg-muted flex-shrink-0" />
          <div>
            <p className="font-semibold text-foreground text-sm">{product.name}</p>
            <p className="text-primary font-bold">{fmt(product.pricePerBal)}<span className="text-xs font-normal text-muted-foreground">/bal</span></p>
          </div>
        </div>

        <div className="mb-5">
          <label className="text-sm font-medium text-foreground block mb-2">Jumlah <span className="text-muted-foreground font-normal">(min. 100 bal)</span></label>
          <div className="flex items-center gap-3">
            <button
              onClick={() => setQty(q => Math.max(100, q - 10))}
              className="w-10 h-10 rounded-xl border border-border bg-card hover:bg-muted flex items-center justify-center cursor-pointer transition-colors"
            >
              <Minus className="w-4 h-4" />
            </button>
            <input
              type="number" value={qty}
              onChange={e => setQty(Math.max(100, parseInt(e.target.value) || 100))}
              min={100}
              className="flex-1 text-center px-3 py-2 rounded-xl border border-border bg-input-background text-foreground font-medium focus:outline-none focus:ring-2 focus:ring-ring/40 focus:border-primary"
            />
            <button
              onClick={() => setQty(q => q + 10)}
              className="w-10 h-10 rounded-xl border border-border bg-card hover:bg-muted flex items-center justify-center cursor-pointer transition-colors"
            >
              <Plus className="w-4 h-4" />
            </button>
          </div>
        </div>

        <div className="bg-muted/50 rounded-xl p-3 mb-5 flex flex-col gap-1.5 text-sm">
          <div className="flex justify-between">
            <span className="text-muted-foreground">Subtotal</span>
            <span className="font-semibold text-foreground">{fmt(subtotal)}</span>
          </div>
          <div className="flex justify-between text-xs text-muted-foreground">
            <span>Jika pilih DP 65%</span>
            <span>{fmt(dp)} sekarang</span>
          </div>
        </div>

        <div className="flex gap-3">
          <Btn variant="outline" className="flex-1" onClick={onClose}>Batal</Btn>
          <Btn className="flex-1" onClick={() => onConfirm(qty)}>
            <ShoppingCart className="w-4 h-4" /> Tambah
          </Btn>
        </div>
      </div>
    </div>
  );
}

// ─── QRIS Modal ──────────────────────────────────────────────────────────────
function QrisModal() {
  const [open, setOpen] = useState(false);
  return (
    <>
      <div className="flex items-center justify-between bg-muted/50 border border-border rounded-xl px-4 py-3">
        <div>
          <span className="font-bold text-primary">QRIS</span>
          <p className="text-xs text-muted-foreground mt-0.5">Scan dengan semua e-wallet & m-banking</p>
        </div>
        <button
          onClick={() => setOpen(true)}
          className="flex items-center gap-2 px-3 py-2 bg-primary text-primary-foreground text-xs font-medium rounded-lg hover:bg-[#7a3b10] transition-colors cursor-pointer"
        >
          <div className="w-5 h-5 bg-white rounded flex items-center justify-center flex-shrink-0">
            <svg width="13" height="13" viewBox="0 0 37 37" xmlns="http://www.w3.org/2000/svg" shapeRendering="crispEdges">
              <rect x="1" y="1" width="9" height="9" rx="1" fill="#8B4513"/>
              <rect x="2.5" y="2.5" width="6" height="6" rx="0.4" fill="white"/>
              <rect x="3.5" y="3.5" width="4" height="4" rx="0.3" fill="#8B4513"/>
              <rect x="27" y="1" width="9" height="9" rx="1" fill="#8B4513"/>
              <rect x="28.5" y="2.5" width="6" height="6" rx="0.4" fill="white"/>
              <rect x="29.5" y="3.5" width="4" height="4" rx="0.3" fill="#8B4513"/>
              <rect x="1" y="27" width="9" height="9" rx="1" fill="#8B4513"/>
              <rect x="2.5" y="28.5" width="6" height="6" rx="0.4" fill="white"/>
              <rect x="3.5" y="29.5" width="4" height="4" rx="0.3" fill="#8B4513"/>
            </svg>
          </div>
          Tampilkan QR
        </button>
      </div>

      {open && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center p-4"
          style={{ backdropFilter: "blur(8px)", backgroundColor: "rgba(44,24,16,0.55)" }}
          onClick={() => setOpen(false)}
        >
          <div
            className="bg-white rounded-3xl shadow-2xl p-6 w-full max-w-sm flex flex-col items-center"
            onClick={e => e.stopPropagation()}
          >
            <div className="flex items-center justify-between w-full mb-5">
              <div>
                <h3 className="font-bold text-foreground text-base" style={{ fontFamily: "'Playfair Display', serif" }}>Bayar via QRIS</h3>
                <p className="text-xs text-muted-foreground">a.n. PT Lintang Bakery Nusantara</p>
              </div>
              <button onClick={() => setOpen(false)} className="p-2 rounded-xl hover:bg-muted transition-colors cursor-pointer">
                <X className="w-4 h-4 text-muted-foreground" />
              </button>
            </div>

            <div className="bg-[#FDF6EE] rounded-2xl p-5 border-2 border-secondary mb-5 flex flex-col items-center gap-3 w-full">
              <div className="flex items-center gap-2">
                <div className="w-6 h-6 rounded-full bg-primary flex items-center justify-center">
                  <span className="text-white font-bold text-[10px]" style={{ fontFamily: "'Playfair Display', serif" }}>L</span>
                </div>
                <span className="font-bold text-sm text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Lintang Bakery</span>
              </div>
              <div className="p-3 bg-white rounded-2xl border border-border shadow-sm">
                <svg width="200" height="200" viewBox="0 0 37 37" xmlns="http://www.w3.org/2000/svg" shapeRendering="crispEdges">
                  <rect x="1" y="1" width="9" height="9" rx="1.2" fill="#8B4513"/>
                  <rect x="2.5" y="2.5" width="6" height="6" rx="0.6" fill="white"/>
                  <rect x="3.5" y="3.5" width="4" height="4" rx="0.4" fill="#8B4513"/>
                  <rect x="27" y="1" width="9" height="9" rx="1.2" fill="#8B4513"/>
                  <rect x="28.5" y="2.5" width="6" height="6" rx="0.6" fill="white"/>
                  <rect x="29.5" y="3.5" width="4" height="4" rx="0.4" fill="#8B4513"/>
                  <rect x="1" y="27" width="9" height="9" rx="1.2" fill="#8B4513"/>
                  <rect x="2.5" y="28.5" width="6" height="6" rx="0.6" fill="white"/>
                  <rect x="3.5" y="29.5" width="4" height="4" rx="0.4" fill="#8B4513"/>
                  <rect x="12" y="1" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="14.5" y="1" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="17" y="1" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="20" y="1" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="23" y="1" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="12" y="3.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="17" y="3.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="20" y="3.5" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="14.5" y="6" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="20" y="6" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="23" y="6" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="12" y="8.5" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="17" y="8.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="21.5" y="8.5" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="1" y="12" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="4" y="12" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="7" y="12" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="1" y="14.5" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="6" y="14.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="1" y="17" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="4" y="17" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="1" y="19.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="5.5" y="19.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="1" y="22" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="6" y="22" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="12" y="12" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="15" y="12" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="20" y="12" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="23" y="12" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="12" y="14.5" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="17" y="14.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="21.5" y="14.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="13.5" y="17" width="1.5" height="3" fill="#8B4513"/>
                  <rect x="17" y="17" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="22" y="17" width="1.5" height="3" fill="#8B4513"/>
                  <rect x="12" y="19.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="16" y="19.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="19.5" y="19.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="24.5" y="19.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="12" y="22" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="17" y="22" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="20" y="22" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="27" y="12" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="30" y="12" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="33.5" y="12" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="28.5" y="14.5" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="33.5" y="14.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="27" y="17" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="30" y="17" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="27" y="19.5" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="32" y="19.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="27" y="22" width="1.5" height="3" fill="#8B4513"/>
                  <rect x="30" y="22" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="33.5" y="22" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="12" y="27" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="15" y="27" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="18.5" y="27" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="23" y="27" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="27" y="27" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="32" y="27" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="12" y="29.5" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="17" y="29.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="20" y="29.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="23" y="29.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="28.5" y="29.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="32" y="29.5" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="13.5" y="32" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="17" y="32" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="21.5" y="32" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="24.5" y="32" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="27" y="32" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="30" y="32" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="33.5" y="32" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="12" y="34.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="15" y="34.5" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="20" y="34.5" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="25" y="34.5" width="3" height="1.5" fill="#8B4513"/>
                  <rect x="30" y="34.5" width="1.5" height="1.5" fill="#8B4513"/>
                  <rect x="33.5" y="34.5" width="1.5" height="1.5" fill="#8B4513"/>
                </svg>
              </div>
              <p className="text-[10px] text-muted-foreground font-mono text-center leading-relaxed">
                ID: 000201010211LINTANGBAKERY2024
              </p>
            </div>

            <div className="flex items-center gap-2 text-xs text-muted-foreground bg-muted/60 rounded-xl px-4 py-2.5 w-full justify-center mb-4">
              <Shield className="w-3.5 h-3.5 text-primary flex-shrink-0" />
              Berlaku untuk semua e-wallet & m-banking Indonesia
            </div>

            <button onClick={() => setOpen(false)}
              className="w-full py-2.5 text-sm font-medium text-muted-foreground border border-border rounded-xl hover:bg-muted transition-colors cursor-pointer">
              Tutup
            </button>
          </div>
        </div>
      )}
    </>
  );
}

// ─── Page: Cart ───────────────────────────────────────────────────────────────
function CartPage({ cart, setPage, removeFromCart, updateCartQty, onCheckout }: {
  cart: { product: Product; quantity: number }[];
  setPage: (p: Page) => void;
  removeFromCart: (id: number) => void;
  updateCartQty: (id: number, qty: number) => void;
  onCheckout: (selected: { product: Product; quantity: number }[]) => void;
}) {
  const [selected, setSelected] = useState<Set<number>>(new Set(cart.map(i => i.product.id)));

  const toggle = (id: number) => setSelected(prev => {
    const next = new Set(prev);
    next.has(id) ? next.delete(id) : next.add(id);
    return next;
  });

  const toggleAll = () => {
    selected.size === cart.length ? setSelected(new Set()) : setSelected(new Set(cart.map(i => i.product.id)));
  };

  const selectedItems = cart.filter(i => selected.has(i.product.id));
  const subtotal = selectedItems.reduce((a, i) => a + i.product.pricePerBal * i.quantity, 0);

  if (cart.length === 0) {
    return (
      <div className="max-w-2xl mx-auto px-4 py-24 text-center">
        <ShoppingCart className="w-16 h-16 text-muted-foreground mx-auto mb-4" />
        <h2 className="text-2xl font-bold text-foreground mb-2" style={{ fontFamily: "'Playfair Display', serif" }}>Keranjang Kosong</h2>
        <p className="text-muted-foreground mb-6">Belum ada produk yang ditambahkan.</p>
        <Btn onClick={() => setPage("catalog")}>Lihat Produk</Btn>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 py-12">
      <div className="flex items-center gap-4 mb-8">
        <button onClick={() => setPage("catalog")} className="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors cursor-pointer">
          <ChevronRight className="w-4 h-4 rotate-180" /> Lanjut Belanja
        </button>
        <span className="text-border">|</span>
        <h1 className="text-2xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>
          Keranjang <span className="text-muted-foreground text-base font-normal">({cart.length} produk)</span>
        </h1>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Item list */}
        <div className="lg:col-span-2 flex flex-col gap-3">
          {/* Select all */}
          <div className="flex items-center gap-3 bg-card border border-border rounded-2xl px-5 py-3">
            <input type="checkbox" checked={selected.size === cart.length} onChange={toggleAll}
              className="w-4 h-4 accent-primary cursor-pointer" />
            <span className="text-sm font-medium text-foreground">Pilih Semua</span>
            {selected.size > 0 && (
              <span className="ml-auto text-xs text-muted-foreground">{selected.size} dipilih</span>
            )}
          </div>

          {cart.map(item => (
            <div key={item.product.id} className={`bg-card border rounded-2xl p-4 transition-all ${selected.has(item.product.id) ? "border-primary/30 shadow-sm" : "border-border opacity-60"}`}>
              <div className="flex items-start gap-4">
                <input type="checkbox" checked={selected.has(item.product.id)} onChange={() => toggle(item.product.id)}
                  className="w-4 h-4 accent-primary cursor-pointer mt-1 flex-shrink-0" />
                <img src={item.product.image} alt={item.product.name}
                  className="w-16 h-16 rounded-xl object-cover bg-muted flex-shrink-0" />
                <div className="flex-1 min-w-0">
                  <div className="flex items-start justify-between gap-2">
                    <div>
                      <h3 className="font-semibold text-foreground text-sm" style={{ fontFamily: "'Playfair Display', serif" }}>{item.product.name}</h3>
                      <p className="text-xs text-muted-foreground mt-0.5">{item.product.category}</p>
                      <p className="text-primary font-bold mt-1">{fmt(item.product.pricePerBal)}<span className="text-xs font-normal text-muted-foreground">/bal</span></p>
                    </div>
                    <button onClick={() => removeFromCart(item.product.id)}
                      className="p-1.5 rounded-lg hover:bg-destructive/10 hover:text-destructive text-muted-foreground transition-colors cursor-pointer flex-shrink-0">
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>
                  <div className="flex items-center justify-between mt-3">
                    <div className="flex items-center gap-2">
                      <button onClick={() => updateCartQty(item.product.id, Math.max(100, item.quantity - 10))}
                        className="w-8 h-8 rounded-lg border border-border hover:bg-muted flex items-center justify-center cursor-pointer transition-colors">
                        <Minus className="w-3 h-3" />
                      </button>
                      <input type="number" value={item.quantity}
                        onChange={e => updateCartQty(item.product.id, Math.max(100, parseInt(e.target.value) || 100))}
                        className="w-16 text-center text-sm px-2 py-1.5 rounded-lg border border-border bg-input-background focus:outline-none focus:ring-2 focus:ring-ring/40 focus:border-primary"
                      />
                      <button onClick={() => updateCartQty(item.product.id, item.quantity + 10)}
                        className="w-8 h-8 rounded-lg border border-border hover:bg-muted flex items-center justify-center cursor-pointer transition-colors">
                        <Plus className="w-3 h-3" />
                      </button>
                      <span className="text-xs text-muted-foreground">bal</span>
                    </div>
                    <p className="font-bold text-foreground text-sm">{fmt(item.product.pricePerBal * item.quantity)}</p>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Summary */}
        <div>
          <div className="bg-card border border-border rounded-2xl p-5 sticky top-24">
            <h3 className="font-semibold text-foreground mb-4" style={{ fontFamily: "'Playfair Display', serif" }}>Ringkasan</h3>
            {selectedItems.length === 0 ? (
              <p className="text-sm text-muted-foreground mb-4">Belum ada produk yang dipilih.</p>
            ) : (
              <div className="flex flex-col gap-2 mb-4 text-sm">
                {selectedItems.map(i => (
                  <div key={i.product.id} className="flex justify-between">
                    <span className="text-muted-foreground truncate mr-2">{i.product.name} ({i.quantity} bal)</span>
                    <span className="font-medium flex-shrink-0">{fmt(i.product.pricePerBal * i.quantity)}</span>
                  </div>
                ))}
                <div className="border-t border-border pt-2 mt-1 flex justify-between font-bold">
                  <span>Subtotal</span>
                  <span className="text-primary">{fmt(subtotal)}</span>
                </div>
                <p className="text-xs text-muted-foreground">Ongkir dihitung saat checkout</p>
              </div>
            )}
            <Btn className="w-full" disabled={selectedItems.length === 0}
              onClick={() => onCheckout(selectedItems)}>
              Checkout ({selectedItems.length} produk) <ArrowRight className="w-4 h-4" />
            </Btn>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Page: Checkout ───────────────────────────────────────────────────────────
function CheckoutPage({ setPage, cart, removeCheckedOutItems, addOrder }: {
  setPage: (p: Page) => void;
  cart: { product: Product; quantity: number }[];
  removeCheckedOutItems: (items: { product: Product; quantity: number }[]) => void;
  addOrder: (order: Order) => void;
}) {
  const [step, setStep] = useState(0);
  const [nama, setNama] = useState("Toko Oleh-Oleh Makmur");
  const [telepon, setTelepon] = useState("089618512542");
  const [alamat, setAlamat] = useState("Jl. Leuwi Anyar No. 18 RT 001/RW 003, Sukamanah");
  const [provinsi, setProvinsi] = useState("Jawa Barat");
  const [kota, setKota] = useState("Kab. Tasikmalaya");
  const [paymentMethod, setPaymentMethod] = useState<"full" | "dp">("full");
  const [copyFeedback, setCopyFeedback] = useState<string | null>(null);
  const [uploaded, setUploaded] = useState(false);
  const [proofUrl, setProofUrl] = useState<string | null>(null);
  const [newOrderId, setNewOrderId] = useState("");
  const fileRef = useRef<HTMLInputElement>(null);

  const steps = ["Pengiriman", "Pembayaran", "Selesai"];
  const kotaOptions = provinsi ? PROVINCES[provinsi] || [] : [];
  const totalBal = cart.reduce((acc, item) => acc + item.quantity, 0);
  const shippingCost = totalBal * 10000;
  const subtotal = cart.reduce((acc, item) => acc + item.product.pricePerBal * item.quantity, 0);
  const total = subtotal + shippingCost;
  const dp = Math.round(total * 0.65);
  const remaining = total - dp;

  const copyToClipboard = (text: string, label: string) => {
    navigator.clipboard.writeText(text);
    setCopyFeedback(label);
    setTimeout(() => setCopyFeedback(null), 2000);
  };

  if (cart.length === 0) {
    return (
      <div className="max-w-2xl mx-auto px-4 py-24 text-center">
        <ShoppingCart className="w-16 h-16 text-muted-foreground mx-auto mb-4" />
        <h2 className="text-2xl font-bold text-foreground mb-2" style={{ fontFamily: "'Playfair Display', serif" }}>Keranjang Kosong</h2>
        <p className="text-muted-foreground mb-6">Belum ada produk yang dipilih.</p>
        <Btn onClick={() => setPage("catalog")}>Lihat Produk</Btn>
      </div>
    );
  }

  return (
    <div className="max-w-5xl mx-auto px-4 sm:px-6 py-12">
      <div className="flex items-center gap-4 mb-8">
        <button
          onClick={() => setPage("catalog")}
          className="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
        >
          <ChevronRight className="w-4 h-4 rotate-180" /> Kembali ke Katalog
        </button>
        <span className="text-border">|</span>
        <h1 className="text-2xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Checkout</h1>
      </div>

      {/* Step Indicator */}
      <div className="flex items-center mb-10">
        {steps.map((s, i) => (
          <div key={s} className="flex items-center flex-1 last:flex-none">
            <div className={`flex items-center gap-2 ${i <= step ? "text-primary" : "text-muted-foreground"}`}>
              <div className={`w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all ${i < step ? "bg-primary border-primary text-white" : i === step ? "border-primary text-primary bg-primary/10" : "border-border text-muted-foreground"}`}>
                {i < step ? <Check className="w-4 h-4" /> : i + 1}
              </div>
              <span className="text-sm font-medium hidden sm:block">{s}</span>
            </div>
            {i < steps.length - 1 && <div className={`flex-1 h-px mx-3 transition-colors ${i < step ? "bg-primary" : "bg-border"}`} />}
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <div className="lg:col-span-3">
          {/* Step 0: Pengiriman */}
          {step === 0 && (
            <div className="bg-card border border-border rounded-2xl p-6">
              <h2 className="font-semibold text-foreground text-lg mb-5" style={{ fontFamily: "'Playfair Display', serif" }}>Informasi Pengiriman</h2>
              <div className="flex flex-col gap-4">
                <Input label="Nama Penerima / Toko" value={nama} onChange={setNama} required />
                <Input label="No. Telepon" type="tel" value={telepon} onChange={setTelepon} required />
                <Input label="Alamat Lengkap" value={alamat} onChange={setAlamat} required />
                <div className="grid grid-cols-2 gap-3">
                  <Select label="Provinsi" value={provinsi} onChange={v => { setProvinsi(v); setKota(""); }}
                    options={Object.keys(PROVINCES)} required />
                  <Select label="Kota / Kabupaten" value={kota} onChange={setKota}
                    options={kotaOptions} placeholder="— Pilih Kota —" required />
                </div>
                <Btn size="lg" className="w-full mt-2" onClick={() => setStep(1)}>
                  Lanjut ke Pembayaran <ChevronRight className="w-4 h-4" />
                </Btn>
              </div>
            </div>
          )}

          {/* Step 1: Pembayaran */}
          {step === 1 && (
            <div className="flex flex-col gap-5">
              <div className="bg-card border border-border rounded-2xl p-6">
                <h2 className="font-semibold text-foreground text-lg mb-5" style={{ fontFamily: "'Playfair Display', serif" }}>Metode Pembayaran</h2>
                <div className="flex flex-col gap-3 mb-6">
                  {(["full", "dp"] as const).map(m => (
                    <label key={m} className={`flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors ${paymentMethod === m ? "border-primary bg-primary/5" : "border-border hover:border-primary/40"}`}>
                      <input type="radio" name="payment" value={m} checked={paymentMethod === m}
                        onChange={() => setPaymentMethod(m)} className="mt-1 accent-primary" />
                      <div>
                        <p className="font-medium text-foreground text-sm">
                          {m === "full" ? "Bayar Penuh" : "DP 65% (Uang Muka)"}
                        </p>
                        {m === "full" ? (
                          <p className="text-xl font-bold text-primary mt-1">{fmt(total)}</p>
                        ) : (
                          <div className="mt-1">
                            <p className="text-sm text-muted-foreground">Total: <span className="text-foreground font-medium">{fmt(total)}</span></p>
                            <p className="text-lg font-bold text-accent">DP 65%: {fmt(dp)}</p>
                            <p className="text-sm text-muted-foreground">Sisa 35% ({fmt(remaining)}) dibayar cash saat barang sampai</p>
                          </div>
                        )}
                      </div>
                    </label>
                  ))}
                </div>

                {/* Bank Info */}
                <div className="flex flex-col gap-3 mb-6">
                  <p className="text-sm font-medium text-foreground">Transfer ke rekening:</p>
                  {[
                    { bank: "BRI", norek: "1234-5678-9012-3456", nama: "PT Lintang Bakery Nusantara" },
                    { bank: "BCA", norek: "8765-4321-0987-6543", nama: "PT Lintang Bakery Nusantara" },
                  ].map(b => (
                    <div key={b.bank} className="flex items-center justify-between bg-muted/50 border border-border rounded-xl px-4 py-3">
                      <div>
                        <span className="font-bold text-primary">{b.bank}</span>
                        <p className="text-sm font-mono text-foreground">{b.norek}</p>
                        <p className="text-xs text-muted-foreground">a.n. {b.nama}</p>
                      </div>
                      <button onClick={() => copyToClipboard(b.norek.replace(/-/g, ""), b.bank)}
                        className={`flex items-center gap-1.5 text-xs px-3 py-2 rounded-lg transition-colors cursor-pointer ${copyFeedback === b.bank ? "bg-green-100 text-green-700" : "bg-card border border-border text-muted-foreground hover:text-primary"}`}>
                        {copyFeedback === b.bank ? <><Check className="w-3 h-3" /> Disalin</> : <><Copy className="w-3 h-3" /> Salin</>}
                      </button>
                    </div>
                  ))}
                  <QrisModal />
                </div>

                {/* Upload */}
                <div>
                  <p className="text-sm font-medium text-foreground mb-2">Upload Bukti Pembayaran</p>
                  <div
                    onClick={() => fileRef.current?.click()}
                    className={`border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-colors ${uploaded ? "border-primary bg-primary/5" : "border-border hover:border-primary/50 hover:bg-muted/50"}`}>
                    <input ref={fileRef} type="file" accept="image/*" className="hidden" onChange={e => {
                      const file = e.target.files?.[0];
                      if (file) { setUploaded(true); setProofUrl(URL.createObjectURL(file)); }
                    }} />
                    {uploaded ? (
                      <div className="flex flex-col items-center gap-2 text-primary">
                        <CheckCircle className="w-8 h-8" />
                        <span className="text-sm font-medium">Bukti berhasil diupload</span>
                      </div>
                    ) : (
                      <div className="flex flex-col items-center gap-2 text-muted-foreground">
                        <Upload className="w-8 h-8" />
                        <span className="text-sm">Klik untuk upload foto/screenshot bukti transfer</span>
                        <span className="text-xs">PNG, JPG maks 5MB</span>
                      </div>
                    )}
                  </div>
                </div>
              </div>

              <div className="flex gap-3">
                <Btn variant="outline" onClick={() => setStep(0)} className="flex-1">Kembali</Btn>
                <Btn className="flex-1" onClick={() => {
                  const id = `LB-${new Date().getFullYear()}-${String(Date.now()).slice(-4)}`;
                  setNewOrderId(id);
                  const subtotalItems = cart.reduce((a, i) => a + i.product.pricePerBal * i.quantity, 0);
                  const totalWithShipping = subtotalItems + 50000;
                  const newOrder: Order = {
                    id,
                    date: new Date().toISOString().split("T")[0],
                    buyer: nama,
                    phone: telepon,
                    address: alamat,
                    city: kota,
                    province: provinsi,
                    items: cart.map(i => ({ product: i.product, quantity: i.quantity, subtotal: i.product.pricePerBal * i.quantity })),
                    total: subtotalItems,
                    dp: paymentMethod === "dp" ? Math.round(totalWithShipping * 0.65) : 0,
                    remaining: paymentMethod === "dp" ? Math.round(totalWithShipping * 0.35) : 0,
                    paymentType: paymentMethod,
                    status: "Menunggu Konfirmasi",
                    paymentProof: proofUrl || undefined,
                    shippingCost: cart.reduce((a, i) => a + i.quantity, 0) * 10000,
                  };
                  addOrder(newOrder);
                  setStep(2);
                }} disabled={!uploaded}>
                  Konfirmasi Pesanan <Check className="w-4 h-4" />
                </Btn>
              </div>
            </div>
          )}

          {/* Step 2: Selesai */}
          {step === 2 && (
            <div className="bg-card border border-border rounded-2xl p-10 text-center">
              <div className="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-5">
                <CheckCircle className="w-10 h-10 text-primary" />
              </div>
              <h2 className="text-2xl font-bold text-foreground mb-2" style={{ fontFamily: "'Playfair Display', serif" }}>Transaksi Berhasil!</h2>
              <p className="text-muted-foreground mb-1">Pesanan Anda telah diterima.</p>
              <p className="text-sm text-muted-foreground mb-6">Kami akan memproses setelah pembayaran dikonfirmasi. Estimasi produksi 5–7 hari kerja.</p>
              <div className="bg-muted/60 rounded-xl p-4 text-left mb-6">
                <div className="grid grid-cols-2 gap-2 text-sm">
                  <span className="text-muted-foreground">No. Pesanan</span><span className="font-medium">#{newOrderId}</span>
                  <span className="text-muted-foreground">Status</span><span className="font-medium text-amber-600">Menunggu Konfirmasi</span>
                  <span className="text-muted-foreground">Tujuan</span><span className="font-medium">{kota}, {provinsi}</span>
                </div>
              </div>
              <Btn className="w-full" onClick={() => { removeCheckedOutItems(cart); setPage("buyer-dashboard"); }}>
                Lihat Riwayat Pesanan <ArrowRight className="w-4 h-4" />
              </Btn>
            </div>
          )}
        </div>

        {/* Order Summary Sidebar */}
        <div className="lg:col-span-2">
          <div className="bg-card border border-border rounded-2xl p-5 sticky top-24">
            <h3 className="font-semibold text-foreground mb-4" style={{ fontFamily: "'Playfair Display', serif" }}>Ringkasan Pesanan</h3>
            <div className="flex flex-col gap-3 mb-5">
              {cart.map(item => (
                <div key={item.product.id} className="flex gap-3">
                  <img src={item.product.image} alt={item.product.name} className="w-14 h-14 rounded-xl object-cover bg-muted flex-shrink-0" />
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium text-foreground leading-snug">{item.product.name}</p>
                    <p className="text-xs text-muted-foreground">{item.quantity} bal × {fmt(item.product.pricePerBal)}</p>
                    <p className="text-sm font-semibold text-primary">{fmt(item.quantity * item.product.pricePerBal)}</p>
                  </div>
                </div>
              ))}
            </div>
            <div className="border-t border-border pt-4 flex flex-col gap-2 text-sm">
              <div className="flex justify-between">
                <span className="text-muted-foreground">Subtotal</span>
                <span>{fmt(subtotal)}</span>
              </div>
              <div className="flex justify-between">
                <div>
                  <span className="text-muted-foreground">Ongkir</span>
                  <span className="text-xs text-muted-foreground ml-1.5 bg-muted px-1.5 py-0.5 rounded">Rp10.000/bal × {totalBal} bal</span>
                </div>
                <span>{fmt(shippingCost)}</span>
              </div>
              {step === 1 && paymentMethod === "dp" && (
                <>
                  <div className="flex justify-between text-accent font-semibold">
                    <span>DP 65%</span>
                    <span>{fmt(dp)}</span>
                  </div>
                  <div className="flex justify-between text-muted-foreground">
                    <span>Sisa Pelunasan</span>
                    <span>{fmt(remaining)}</span>
                  </div>
                </>
              )}
              <div className="flex justify-between font-bold text-base border-t border-border pt-2 mt-1">
                <span>Total</span>
                <span className="text-primary">{fmt(total)}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Page: Buyer Dashboard ────────────────────────────────────────────────────
function ProfileCard() {
  const [editing, setEditing] = useState(false);
  const [nama, setNama] = useState("Toko Oleh-Oleh Makmur");
  const [email, setEmail] = useState("buyer@test.com");
  const [telepon, setTelepon] = useState("089618512542");
  const [alamat, setAlamat] = useState("Jl. Leuwi Anyar No. 18 RT 001/RW 003, Sukamanah");
  const [provinsi, setProvinsi] = useState("Jawa Barat");
  const [kota, setKota] = useState("Kab. Tasikmalaya");

  const Field = ({ label, value }: { label: string; value: string }) => (
    <div className="flex flex-col gap-0.5">
      <span className="text-xs text-muted-foreground">{label}</span>
      <span className="text-sm font-medium text-foreground">{value}</span>
    </div>
  );

  return (
    <div className="flex justify-center">
      <div className="bg-card border border-border rounded-2xl p-6 w-full max-w-lg">
        <div className="flex items-center justify-between mb-5">
          <h3 className="font-semibold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Informasi Akun</h3>
          {!editing ? (
            <button
              onClick={() => setEditing(true)}
              className="flex items-center gap-1.5 text-sm text-primary font-medium px-3 py-1.5 rounded-lg border border-primary/20 hover:bg-primary/8 transition-colors cursor-pointer"
            >
              <Edit className="w-3.5 h-3.5" /> Edit
            </button>
          ) : (
            <button
              onClick={() => setEditing(false)}
              className="flex items-center gap-1.5 text-sm text-muted-foreground px-3 py-1.5 rounded-lg border border-border hover:bg-muted transition-colors cursor-pointer"
            >
              <X className="w-3.5 h-3.5" /> Batal
            </button>
          )}
        </div>

        {editing ? (
          <div className="flex flex-col gap-4">
            <Input label="Nama Toko / Usaha" value={nama} onChange={setNama} />
            <Input label="Email" type="email" value={email} onChange={setEmail} />
            <Input label="No. Telepon" value={telepon} onChange={setTelepon} />
            <Input label="Alamat" value={alamat} onChange={setAlamat} />
            <div className="grid grid-cols-2 gap-3">
              <Select label="Provinsi" value={provinsi} onChange={v => { setProvinsi(v); setKota(""); }} options={Object.keys(PROVINCES)} />
              <Select label="Kota" value={kota} onChange={setKota} options={PROVINCES[provinsi] || []} />
            </div>
            <Btn className="mt-1" onClick={() => setEditing(false)}>
              <Check className="w-4 h-4" /> Simpan Perubahan
            </Btn>
          </div>
        ) : (
          <div className="flex flex-col gap-4">
            <div className="flex items-center gap-4 pb-4 border-b border-border">
              <div className="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                <User className="w-7 h-7 text-primary" />
              </div>
              <div>
                <p className="font-semibold text-foreground">{nama}</p>
                <p className="text-sm text-muted-foreground">{email}</p>
              </div>
            </div>
            <div className="grid grid-cols-2 gap-x-6 gap-y-4">
              <Field label="No. Telepon" value={telepon} />
              <Field label="Provinsi" value={provinsi} />
              <Field label="Kota / Kabupaten" value={kota} />
            </div>
            <div className="pt-1">
              <Field label="Alamat" value={alamat} />
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

function BuyerDashboard({ setPage, orders }: { setPage: (p: Page) => void; orders: Order[] }) {
  const [activeTab, setActiveTab] = useState<"orders" | "profile">("orders");
  const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
  const buyerOrders = orders.filter(o => o.buyer === "Toko Oleh-Oleh Makmur");

  return (
    <div className="max-w-6xl mx-auto px-4 sm:px-6 py-10">
      <div className="flex items-center gap-4 mb-8">
        <div className="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center">
          <User className="w-7 h-7 text-primary" />
        </div>
        <div>
          <h1 className="text-xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Toko Oleh-Oleh Makmur</h1>
          <p className="text-sm text-muted-foreground">buyer@test.com • Kab. Tasikmalaya, Jawa Barat</p>
        </div>
      </div>

      <div className="flex gap-1 border-b border-border mb-7">
        {[["orders", "Riwayat Pesanan"], ["profile", "Profil Saya"]].map(([key, label]) => (
          <button key={key} onClick={() => setActiveTab(key as "orders" | "profile")}
            className={`px-5 py-3 text-sm font-medium transition-colors cursor-pointer border-b-2 -mb-px ${activeTab === key ? "border-primary text-primary" : "border-transparent text-muted-foreground hover:text-foreground"}`}>
            {label}
          </button>
        ))}
      </div>

      {activeTab === "orders" && (
        selectedOrder ? (
          <div className="bg-card border border-border rounded-2xl p-6">
            <div className="flex items-center justify-between mb-6">
              <button onClick={() => setSelectedOrder(null)} className="flex items-center gap-2 text-muted-foreground hover:text-foreground text-sm cursor-pointer">
                <ChevronRight className="w-4 h-4 rotate-180" /> Kembali
              </button>
              <StatusBadge status={selectedOrder.status} />
            </div>
            <h2 className="font-bold text-lg text-foreground mb-1" style={{ fontFamily: "'Playfair Display', serif" }}>
              Pesanan #{selectedOrder.id}
            </h2>
            <p className="text-sm text-muted-foreground mb-6">{fmtDate(selectedOrder.date)}</p>

            {/* Tracking */}
            <div className="mb-8">
              <h3 className="font-semibold text-foreground text-sm mb-4">Status Pengiriman</h3>
              <div className="relative">
                {ORDER_STATUSES.map((s, i) => {
                  const currentIdx = ORDER_STATUSES.indexOf(selectedOrder.status);
                  const done = i <= currentIdx;
                  return (
                    <div key={s} className="flex items-start gap-4 pb-4 last:pb-0">
                      <div className="flex flex-col items-center flex-shrink-0">
                        <div className={`w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all ${done ? "bg-primary border-primary" : "bg-card border-border"}`}>
                          {done ? <Check className="w-4 h-4 text-white" /> : <div className="w-2 h-2 rounded-full bg-border" />}
                        </div>
                        {i < ORDER_STATUSES.length - 1 && <div className={`w-0.5 h-6 mt-1 ${done ? "bg-primary" : "bg-border"}`} />}
                      </div>
                      <div className="pt-1">
                        <p className={`text-sm font-medium ${done ? "text-foreground" : "text-muted-foreground"}`}>{s}</p>
                        {i === currentIdx && <p className="text-xs text-primary mt-0.5">Status saat ini</p>}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="bg-muted/50 rounded-xl p-4">
                <h4 className="font-medium text-foreground text-sm mb-3">Produk Dipesan</h4>
                {selectedOrder.items.map(item => (
                  <div key={item.product.id} className="flex gap-3">
                    <img src={item.product.image} alt={item.product.name} className="w-12 h-12 rounded-lg object-cover bg-muted" />
                    <div>
                      <p className="text-sm font-medium">{item.product.name}</p>
                      <p className="text-xs text-muted-foreground">{item.quantity} bal × {fmt(item.product.pricePerBal)}</p>
                      <p className="text-sm font-semibold text-primary">{fmt(item.subtotal)}</p>
                    </div>
                  </div>
                ))}
              </div>
              <div className="bg-muted/50 rounded-xl p-4">
                <h4 className="font-medium text-foreground text-sm mb-3">Rincian Pembayaran</h4>
                <div className="flex flex-col gap-1.5 text-sm">
                  <div className="flex justify-between"><span className="text-muted-foreground">Subtotal</span><span>{fmt(selectedOrder.total)}</span></div>
                  <div className="flex justify-between"><span className="text-muted-foreground">Ongkir</span><span>{fmt(selectedOrder.shippingCost)}</span></div>
                  {selectedOrder.paymentType === "dp" && (
                    <>
                      <div className="flex justify-between text-accent font-medium"><span>DP 65%</span><span>{fmt(selectedOrder.dp)}</span></div>
                      <div className="flex justify-between text-muted-foreground"><span>Sisa Pelunasan</span><span>{fmt(selectedOrder.remaining)}</span></div>
                    </>
                  )}
                  <div className="flex justify-between font-bold border-t border-border pt-2 mt-1">
                    <span>Total</span><span className="text-primary">{fmt(selectedOrder.total + selectedOrder.shippingCost)}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        ) : buyerOrders.length === 0 ? (
          <div className="text-center py-16 text-muted-foreground">
            <ShoppingBag className="w-12 h-12 mx-auto mb-3 opacity-30" />
            <p className="font-medium">Belum ada pesanan</p>
            <p className="text-sm mt-1">Yuk mulai pesan kue kering kami!</p>
            <Btn className="mt-4" onClick={() => setPage("catalog")}>Lihat Produk</Btn>
          </div>
        ) : (
          <div className="flex flex-col gap-4">
            {buyerOrders.map(order => (
              <div key={order.id} className="bg-card border border-border rounded-2xl p-5 hover:shadow-md transition-shadow">
                <div className="flex flex-wrap items-center justify-between gap-3 mb-3">
                  <div>
                    <span className="font-semibold text-foreground">#{order.id}</span>
                    <span className="text-muted-foreground text-sm ml-3">{fmtDate(order.date)}</span>
                  </div>
                  <StatusBadge status={order.status} />
                </div>
                <div className="flex flex-wrap gap-2 mb-4">
                  {order.items.map(item => (
                    <div key={item.product.id} className="flex items-center gap-2 text-sm">
                      <img src={item.product.image} alt={item.product.name} className="w-8 h-8 rounded-lg object-cover bg-muted" />
                      <span>{item.product.name} — {item.quantity} bal</span>
                    </div>
                  ))}
                </div>
                <div className="flex items-center justify-between">
                  <span className="font-bold text-primary">{fmt(order.total + order.shippingCost)}</span>
                  <Btn size="sm" variant="outline" onClick={() => setSelectedOrder(order)}>
                    <Eye className="w-3.5 h-3.5" /> Lihat Detail
                  </Btn>
                </div>
              </div>
            ))}
          </div>
        )
      )}

      {activeTab === "profile" && (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2">
            <ProfileCard />
          </div>
          <div className="flex flex-col gap-4">
            {/* Ringkasan Akun */}
            <div className="bg-card border border-border rounded-2xl p-5">
              <h3 className="font-semibold text-foreground text-sm mb-4" style={{ fontFamily: "'Playfair Display', serif" }}>Ringkasan Akun</h3>
              <div className="flex flex-col gap-3">
                {[
                  { label: "Total Pesanan", value: String(buyerOrders.length), icon: <ShoppingBag className="w-4 h-4" /> },
                  { label: "Pesanan Aktif", value: String(buyerOrders.filter(o => !["Selesai/Lunas"].includes(o.status)).length), icon: <Clock className="w-4 h-4" /> },
                  { label: "Pesanan Selesai", value: String(buyerOrders.filter(o => o.status === "Selesai/Lunas").length), icon: <CheckCircle className="w-4 h-4" /> },
                ].map(item => (
                  <div key={item.label} className="flex items-center justify-between py-2.5 border-b border-border last:border-0">
                    <div className="flex items-center gap-2.5 text-muted-foreground">
                      <span className="text-primary">{item.icon}</span>
                      <span className="text-sm">{item.label}</span>
                    </div>
                    <span className="font-bold text-foreground">{item.value}</span>
                  </div>
                ))}
              </div>
            </div>

            {/* Info Pemesanan */}
            <div className="bg-secondary/30 border border-secondary rounded-2xl p-5">
              <h3 className="font-semibold text-foreground text-sm mb-3" style={{ fontFamily: "'Playfair Display', serif" }}>Info Pemesanan</h3>
              <div className="flex flex-col gap-2.5 text-sm text-muted-foreground">
                <div className="flex items-start gap-2">
                  <div className="w-1.5 h-1.5 rounded-full bg-primary mt-1.5 flex-shrink-0" />
                  <span>Minimum order <strong className="text-foreground">100 bal</strong> per produk</span>
                </div>
                <div className="flex items-start gap-2">
                  <div className="w-1.5 h-1.5 rounded-full bg-primary mt-1.5 flex-shrink-0" />
                  <span>Pilih <strong className="text-foreground">Bayar Penuh</strong> atau <strong className="text-foreground">DP 65%</strong></span>
                </div>
                <div className="flex items-start gap-2">
                  <div className="w-1.5 h-1.5 rounded-full bg-primary mt-1.5 flex-shrink-0" />
                  <span>Sisa 35% dibayar cash saat barang sampai</span>
                </div>
                <div className="flex items-start gap-2">
                  <div className="w-1.5 h-1.5 rounded-full bg-primary mt-1.5 flex-shrink-0" />
                  <span>Hubungi kami di <strong className="text-foreground">+62 896-1851-2542</strong></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

// ─── Admin Sidebar ────────────────────────────────────────────────────────────
function AdminLayout({ children, page, setPage, onLogout, orders }: {
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
function BestSellerChart({ orders, trx }: { orders: Order[]; trx: OfflineTransaction[] }) {
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

function TrendChart() {
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

function AdminDashboard({ setPage, orders, trx, stocks, onReset }: { setPage: (p: Page) => void; orders: Order[]; trx: OfflineTransaction[]; stocks: StockItem[]; onReset: () => void }) {
  const today = new Date().toISOString().split("T")[0];
  const thisMonth = today.slice(0, 7);
  const { low: lowStock, expiring: expiringStock } = stockAlerts(stocks ?? []);

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

      {(lowStock.length > 0 || expiringStock.length > 0) && (
        <div className="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6 flex items-start gap-3">
          <AlertTriangle className="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" />
          <div className="flex-1 text-sm">
            <p className="font-semibold text-amber-800 mb-1">Peringatan Stok</p>
            {lowStock.length > 0 && (
              <p className="text-amber-700">{lowStock.map(s => `${s.name} ${stockStatus(s).toLowerCase()} (${s.stock} bal)`).join(", ")}.</p>
            )}
            {expiringStock.length > 0 && (
              <p className="text-amber-700 mt-0.5">Dekat kedaluwarsa: {expiringStock.map(e => `${e.item.name} (${e.days < 0 ? "sudah lewat" : `${e.days} hari lagi`})`).join(", ")}.</p>
            )}
          </div>
          <button onClick={() => setPage("admin-stock")} className="text-xs font-medium text-amber-800 underline cursor-pointer flex-shrink-0">Lihat stok</button>
        </div>
      )}

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
          { label: "Lihat Stok", sub: `${lowStock.length} produk menipis`, target: "admin-stock", icon: <Archive className="w-5 h-5" /> },
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
function AdminOrders({ orders, setOrders }: { orders: Order[]; setOrders: React.Dispatch<React.SetStateAction<Order[]>> }) {
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
function AdminTransactions({ trx, setTrx, stocks, recordMovements }: {
  trx: OfflineTransaction[];
  setTrx: React.Dispatch<React.SetStateAction<OfflineTransaction[]>>;
  stocks: StockItem[];
  recordMovements: (list: MovementInput[]) => void;
}) {
  const [voidTarget, setVoidTarget] = useState<OfflineTransaction | null>(null);
  const [voidReason, setVoidReason] = useState("");
  const [voidError, setVoidError] = useState("");
  const [showForm, setShowForm] = useState(false);
  const [date, setDate] = useState(new Date().toISOString().split("T")[0]);
  const [buyerName, setBuyerName] = useState("");
  const [notes, setNotes] = useState("");
  const [paymentMethod, setPaymentMethod] = useState<"cash" | "qris-tf">("cash");
  const [items, setItems] = useState<{ productId: number; quantity: number }[]>([{ productId: (stocks[0]?.id ?? 0), quantity: 1 }]);
  const [error, setError] = useState("");

  const addItem = () => setItems(prev => [...prev, { productId: (stocks[0]?.id ?? 0), quantity: 1 }]);
  const removeItem = (i: number) => setItems(prev => prev.filter((_, idx) => idx !== i));
  const updateItem = (i: number, field: "productId" | "quantity", val: number) =>
    setItems(prev => prev.map((it, idx) => idx === i ? { ...it, [field]: val } : it));

  const handleSave = () => {
    const need: Record<number, number> = {};
    items.forEach(it => { need[it.productId] = (need[it.productId] || 0) + it.quantity; });
    for (const [pid, qty] of Object.entries(need)) {
      const stock = stocks.find(s => s.id === Number(pid));
      if (!stock || qty > stock.stock) {
        setError(`Stok ${stock?.name ?? "produk"} tidak cukup (tersisa ${stock?.stock ?? 0} bal).`);
        return;
      }
    }
    setError("");
    const trxId = `TRX-${Date.now()}`;
    const newTrx: OfflineTransaction = {
      id: trxId,
      status: "PAID",
      date,
      buyerName,
      paymentMethod,
      notes,
      items: items.map(it => {
        const p = stocks.find(p => p.id === it.productId)!;
        const prod: Product = { id: p.id, name: p.name, description: p.description, pricePerBal: p.pricePerBal, image: p.image, category: p.category };
        return { product: prod, quantity: it.quantity, subtotal: p.pricePerBal * it.quantity };
      }),
      total: items.reduce((a, it) => {
        const p = stocks.find(p => p.id === it.productId)!;
        return a + p.pricePerBal * it.quantity;
      }, 0),
    };
    setTrx(prev => [newTrx, ...prev]);
    recordMovements(Object.entries(need).map(([pid, qty]) => ({
      productId: Number(pid), type: "Keluar" as const, qty, reason: "Penjualan offline", ref: trxId, date,
    })));
    setBuyerName(""); setNotes(""); setPaymentMethod("cash"); setItems([{ productId: (stocks[0]?.id ?? 0), quantity: 1 }]);
    setShowForm(false);
  };

  const handleVoid = () => {
    if (!voidTarget) return;
    if (!voidReason.trim()) { setVoidError("Alasan pembatalan wajib diisi."); return; }
    const t = voidTarget;
    setTrx(prev => prev.map(x => x.id === t.id ? { ...x, status: "VOID" as const, voidReason: voidReason.trim(), voidDate: todayStr() } : x));
    const back: Record<number, number> = {};
    t.items.forEach(i => { back[i.product.id] = (back[i.product.id] || 0) + i.quantity; });
    recordMovements(Object.entries(back).map(([pid, qty]) => ({
      productId: Number(pid), type: "Masuk" as const, qty, reason: "Pembatalan", ref: t.id, note: voidReason.trim(),
    })));
    setVoidTarget(null); setVoidReason(""); setVoidError("");
  };

  return (
    <div>
      {voidTarget && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4" style={{ backdropFilter: "blur(8px)", backgroundColor: "rgba(44,24,16,0.5)" }} onClick={() => setVoidTarget(null)}>
          <div className="bg-card rounded-2xl border border-border shadow-2xl w-full max-w-md p-6" onClick={e => e.stopPropagation()}>
            <h3 className="font-bold text-foreground mb-1" style={{ fontFamily: "'Playfair Display', serif" }}>Batalkan Transaksi (Void)</h3>
            <p className="text-xs text-muted-foreground mb-4">{voidTarget.id} · {voidTarget.items.map(i => `${i.product.name} (${i.quantity} bal)`).join(", ")}</p>
            <Input label="Alasan pembatalan" value={voidReason} onChange={setVoidReason} placeholder="Misal: pembeli membatalkan" required />
            <p className="text-xs text-muted-foreground mt-2">Stok akan kembali otomatis dan tercatat di Kartu Stok sebagai Masuk - Pembatalan.</p>
            {voidError && <p className="text-xs text-destructive bg-destructive/10 px-3 py-2 rounded-lg mt-3">{voidError}</p>}
            <div className="flex gap-3 mt-5">
              <Btn variant="outline" className="flex-1" onClick={() => { setVoidTarget(null); setVoidReason(""); setVoidError(""); }}>Batal</Btn>
              <Btn variant="danger" className="flex-1" onClick={handleVoid}>Ya, Void</Btn>
            </div>
          </div>
        </div>
      )}
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
                          {stocks.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
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
                    const p = stocks.find(p => p.id === it.productId)!;
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
                {["Tanggal", "Produk", "Bayar", "Total", "Status", "Catatan", "Aksi"].map(h => (
                  <th key={h} className={`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide ${h === 'Total' ? 'text-right' : ''}`}>{h}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {trx.length === 0 ? (
                <tr><td colSpan={7} className="px-4 py-16 text-center text-muted-foreground">
                  <Archive className="w-10 h-10 mx-auto mb-2 opacity-30" />
                  <p>Belum ada transaksi offline.</p>
                </td></tr>
              ) : trx.map((t, i) => (
                <tr key={t.id} className={`border-b border-border last:border-0 hover:bg-muted/20 ${i % 2 === 0 ? "" : "bg-muted/10"} ${t.status === "VOID" ? "opacity-60" : ""}`}>
                  <td className="px-4 py-3 text-sm whitespace-nowrap">{fmtDate(t.date)}</td>
                  
                  <td className="px-4 py-3 text-xs text-muted-foreground">{t.items.map(i => `${i.product.name} (${i.quantity} bal)`).join(", ")}</td>
                  <td className="px-4 py-3">
                    <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border ${
                      t.paymentMethod === "cash" ? "bg-amber-50 text-amber-700 border-amber-200" : "bg-blue-50 text-blue-700 border-blue-200"
                    }`}>
                      {t.paymentMethod === "cash" ? "Tunai" : "QRIS / Transfer"}
                    </span>
                  </td>
                  <td className={`px-4 py-3 font-semibold text-primary text-right ${t.status === "VOID" ? "line-through" : ""}`}>{fmt(t.total)}</td>
                  <td className="px-4 py-3">
                    <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border ${t.status === "VOID" ? "bg-red-50 text-red-700 border-red-200" : "bg-green-50 text-green-700 border-green-200"}`}>
                      {t.status === "VOID" ? "VOID" : "PAID"}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-xs text-muted-foreground">{t.status === "VOID" ? `Void: ${t.voidReason}` : (t.notes || "—")}</td>
                  <td className="px-4 py-3">
                    {t.status !== "VOID" && (
                      <button onClick={() => { setVoidTarget(t); setVoidReason(""); setVoidError(""); }}
                        className="text-xs text-destructive border border-destructive/20 rounded-lg px-2.5 py-1 hover:bg-destructive/5 cursor-pointer">Void</button>
                    )}
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

// ─── Page: Admin Reports ───────────────────────────────────────────────────────
function AdminReports({ orders, trx, movements }: { orders: Order[]; trx: OfflineTransaction[]; movements: StockMovement[] }) {
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

  const inPeriod = (d: string) =>
    filterType === "all" ||
    (filterType === "month" && d.startsWith(filterMonth)) ||
    (filterType === "date" && d === filterDate) ||
    (filterType === "range" && d >= filterStart && d <= filterEnd);
  const lossMoves = movements.filter(m => m.type === "Keluar" && LOSS_REASONS.includes(m.reason) && inPeriod(m.date));
  const totalLoss = lossMoves.reduce((a, m) => a + m.qty * m.unitPrice, 0);
  const totalLossQty = lossMoves.reduce((a, m) => a + m.qty, 0);

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

      <div className="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        {[
          { label: "Total Pendapatan", value: fmt(totalOnline + totalOffline), sub: "Online + Offline", color: "text-green-600 bg-green-50" },
          { label: "Pendapatan Online", value: fmt(totalOnline), sub: `${filteredOrders.length} pesanan`, color: "text-blue-600 bg-blue-50" },
          { label: "Pendapatan Offline", value: fmt(totalOffline), sub: `${filteredTrx.length} transaksi`, color: "text-primary bg-primary/10" },
          { label: "Produk Terlaris", value: topProduct ? topProduct.name.split(" ").slice(0, 2).join(" ") : "-", sub: topProduct ? `${topProduct.total} bal` : "Belum ada data", color: "text-amber-600 bg-amber-50" },
          { label: "Kerugian Stok", value: fmt(totalLoss), sub: `${totalLossQty} bal rusak/kedaluwarsa`, color: "text-red-600 bg-red-50" },
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
      
      {lossMoves.length > 0 && (
        <div className="bg-card border border-border rounded-2xl overflow-hidden mt-5">
          <div className="px-5 py-4 border-b border-border flex items-center justify-between">
            <h3 className="font-semibold text-foreground text-sm" style={{ fontFamily: "'Playfair Display', serif" }}>Kerugian Stok</h3>
            <span className="text-xs text-muted-foreground">{lossMoves.length} catatan</span>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead><tr className="border-b border-border bg-muted/20">
                {["Tanggal", "Produk", "Alasan", "Jumlah", "Nilai"].map(h => (
                  <th key={h} className={`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide ${h === "Nilai" ? "text-right" : ""}`}>{h}</th>
                ))}
              </tr></thead>
              <tbody>
                {lossMoves.map(m => (
                  <tr key={m.id} className="border-b border-border last:border-0">
                    <td className="px-4 py-3 whitespace-nowrap">{fmtDate(m.date)}</td>
                    <td className="px-4 py-3">{m.productName}</td>
                    <td className="px-4 py-3">{m.reason}</td>
                    <td className="px-4 py-3">{m.qty} bal</td>
                    <td className="px-4 py-3 text-right font-semibold text-red-600">{fmt(m.qty * m.unitPrice)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <p className="text-xs text-muted-foreground px-5 py-3 border-t border-border">Nilai dihitung dari harga jual per bal. Gunakan HPP jika data biaya produksi sudah tersedia.</p>
        </div>
      )}

      <div className="hidden print:block mt-12 text-center text-sm text-muted-foreground">
        Dicetak pada: {new Date().toLocaleString('id-ID')}
      </div>
    </div>
  );
}

// ─── Page: Admin Products ─────────────────────────────────────────────────────
function AdminProducts({ products, setProducts, stocks, setStocks }: { products: Product[]; setProducts: React.Dispatch<React.SetStateAction<Product[]>>; stocks: StockItem[]; setStocks: React.Dispatch<React.SetStateAction<StockItem[]>> }) {
  const [showForm, setShowForm] = useState(false);
  const [editing, setEditing] = useState<Product | null>(null);
  const [nama, setNama] = useState("");
  const [deskripsi, setDeskripsi] = useState("");
  const [harga, setHarga] = useState("");
  const [fotoUrl, setFotoUrl] = useState("");
  const [salesChannel, setSalesChannel] = useState<"online" | "offline" | "both">("online");
  const [minStock, setMinStock] = useState("");
  const fotoRef = useRef<HTMLInputElement>(null);
  const [deleteTarget, setDeleteTarget] = useState<Product | null>(null);

  const openAdd = () => { setEditing(null); setNama(""); setDeskripsi(""); setHarga(""); setFotoUrl(""); setSalesChannel("online"); setMinStock(""); setShowForm(true); };
  const openEdit = (p: Product) => { setEditing(p); setNama(p.name); setDeskripsi(p.description); setHarga(String(p.pricePerBal)); setFotoUrl(p.image); setSalesChannel(p.salesChannel || "online"); setMinStock(String(p.minStock ?? stocks.find(s => s.id === p.id)?.minStock ?? DEFAULT_MIN_STOCK)); setShowForm(true); };
  const save = () => {
    const finalImage = fotoUrl || (editing?.image ?? PRODUCTS[0].image);
    const minVal = parseInt(minStock) > 0 ? parseInt(minStock) : DEFAULT_MIN_STOCK;
    if (editing) {
      setProducts(prev => prev.map(p => p.id === editing.id ? { ...p, name: nama, description: deskripsi, pricePerBal: parseInt(harga), image: finalImage, salesChannel, minStock: minVal } : p));
      setStocks(prev => prev.map(s => s.id === editing.id ? { ...s, minStock: minVal } : s));
      if ((salesChannel === "offline" || salesChannel === "both") && !stocks.some(s => s.id === editing.id)) {
        setStocks(prev => [...prev, { id: editing.id, name: nama, description: deskripsi, pricePerBal: parseInt(harga), image: finalImage, category: "Kue Kering", stock: 0, minStock: minVal }]);
      }
      toast.success("Produk berhasil diperbarui", { description: nama });
    } else {
      const newP: Product = { id: Date.now(), name: nama, description: deskripsi, pricePerBal: parseInt(harga), image: finalImage, category: "Kue Kering", salesChannel, minStock: minVal };
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
              {salesChannel !== "online" && (
                <Input label="Stok Minimum (bal)" type="number" value={minStock} onChange={setMinStock} placeholder={String(DEFAULT_MIN_STOCK)} />
              )}
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
function AdminStock({ stocks, movements, recordMovements }: {
  stocks: StockItem[];
  movements: StockMovement[];
  recordMovements: (list: MovementInput[]) => void;
}) {
  const [tab, setTab] = useState<"ringkasan" | "kartu">("ringkasan");
  const [modal, setModal] = useState<"masuk" | "keluar" | null>(null);
  const [fDate, setFDate] = useState(todayStr());
  const [fProduct, setFProduct] = useState("");
  const [fQty, setFQty] = useState("");
  const [fSource, setFSource] = useState("Produksi");
  const [fExpiry, setFExpiry] = useState("");
  const [fReason, setFReason] = useState("");
  const [fNote, setFNote] = useState("");
  const [error, setError] = useState("");
  const [notice, setNotice] = useState("");
  const [cardProduct, setCardProduct] = useState<string>(stocks[0] ? String(stocks[0].id) : "");
  const [cardStart, setCardStart] = useState("");
  const [cardEnd, setCardEnd] = useState("");

  const { low, expiring } = stockAlerts(stocks);

  const openModal = (kind: "masuk" | "keluar") => {
    setFDate(todayStr()); setFProduct(""); setFQty(""); setFSource("Produksi");
    setFExpiry(""); setFReason(""); setFNote(""); setError(""); setModal(kind);
  };
  const flash = (msg: string) => { setNotice(msg); setTimeout(() => setNotice(""), 3000); };

  const submitMasuk = () => {
    const qty = parseInt(fQty);
    if (!fDate) { setError("Tanggal masuk wajib diisi."); return; }
    if (!fProduct) { setError("Pilih produk terlebih dahulu."); return; }
    if (!(qty > 0)) { setError("Jumlah harus lebih dari 0."); return; }
    recordMovements([{ productId: Number(fProduct), type: "Masuk", qty, reason: fSource, note: fNote, date: fDate, expiry: fExpiry || undefined }]);
    setModal(null);
    flash(`Barang masuk ${qty} bal tercatat.`);
  };

  const submitKeluar = () => {
    const qty = parseInt(fQty);
    const item = stocks.find(s => s.id === Number(fProduct));
    if (!fDate) { setError("Tanggal keluar wajib diisi."); return; }
    if (!item) { setError("Pilih produk terlebih dahulu."); return; }
    if (!(qty > 0)) { setError("Jumlah harus lebih dari 0."); return; }
    if (!fReason) { setError("Alasan wajib diisi."); return; }
    if (qty > item.stock) { setError(`Jumlah melebihi stok. Stok tersisa ${item.stock} bal.`); return; }
    recordMovements([{ productId: item.id, type: "Keluar", qty, reason: fReason, note: fNote, date: fDate }]);
    setModal(null);
    flash(LOSS_REASONS.includes(fReason) ? `Barang keluar ${qty} bal tercatat sebagai kerugian.` : `Barang keluar ${qty} bal tercatat.`);
  };

  const cardRows = movements
    .filter(m => String(m.productId) === cardProduct)
    .filter(m => (!cardStart || m.date >= cardStart) && (!cardEnd || m.date <= cardEnd))
    .sort((x, y) => x.date.localeCompare(y.date) || x.ts - y.ts);

  const fieldCls = "w-full px-4 py-2.5 rounded-lg border border-border bg-input-background text-sm focus:outline-none focus:ring-2 focus:ring-ring/40 focus:border-primary";
  const productSelect = (
    <div className="flex flex-col gap-1.5">
      <label className="text-sm font-medium text-foreground">Produk<span className="text-destructive ml-1">*</span></label>
      <select value={fProduct} onChange={e => setFProduct(e.target.value)} className={fieldCls}>
        <option value="">Pilih produk</option>
        {stocks.map(s => <option key={s.id} value={s.id}>{s.name} (stok {s.stock} bal)</option>)}
      </select>
    </div>
  );

  return (
    <div>
      <div className="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
          <h2 className="text-xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Stok Offline</h2>
          <p className="text-xs text-muted-foreground mt-1">Stok barang jadi untuk penjualan toko. Pesanan online dibuat sesuai pesanan (made by order) sehingga tidak memengaruhi stok.</p>
        </div>
        <div className="flex gap-2">
          <Btn size="sm" onClick={() => openModal("masuk")}><Plus className="w-4 h-4" /> Barang Masuk</Btn>
          <Btn size="sm" variant="outline" onClick={() => openModal("keluar")}><Minus className="w-4 h-4" /> Barang Keluar</Btn>
        </div>
      </div>

      {notice && (
        <div className="bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl px-4 py-3 mb-4 flex items-center gap-2">
          <CheckCircle className="w-4 h-4" /> {notice}
        </div>
      )}

      <div className="flex gap-1 border-b border-border mb-6">
        {([["ringkasan", "Ringkasan Stok"], ["kartu", "Kartu Stok"]] as const).map(([key, label]) => (
          <button key={key} onClick={() => setTab(key)}
            className={`px-5 py-3 text-sm font-medium cursor-pointer border-b-2 -mb-px transition-colors ${tab === key ? "border-primary text-primary" : "border-transparent text-muted-foreground hover:text-foreground"}`}>
            {label}
          </button>
        ))}
      </div>

      {tab === "ringkasan" && (
        <>
          {low.length > 0 && (
            <div className="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-4 flex items-start gap-3">
              <AlertTriangle className="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" />
              <div>
                <p className="font-semibold text-amber-800 text-sm">Peringatan Stok</p>
                <p className="text-sm text-amber-700 mt-0.5">
                  {low.map(s => `${s.name} (${stockStatus(s)}, ${s.stock} bal)`).join(", ")}. Rencanakan produksi berikutnya.
                </p>
              </div>
            </div>
          )}
          {expiring.length > 0 && (
            <div className="bg-red-50 border border-red-200 rounded-2xl p-4 mb-4 flex items-start gap-3">
              <Clock className="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" />
              <div>
                <p className="font-semibold text-red-800 text-sm">Mendekati Kedaluwarsa</p>
                <p className="text-sm text-red-700 mt-0.5">
                  {expiring.map(e => `${e.item.name} (${e.days < 0 ? "sudah kedaluwarsa" : `${e.days} hari lagi`})`).join(", ")}. Jual lebih dulu barang yang dekat kedaluwarsa.
                </p>
              </div>
            </div>
          )}

          <div className="bg-card border border-border rounded-2xl overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-border bg-muted/30">
                    {["Produk", "Stok Saat Ini", "Stok Minimum", "Status", "Kedaluwarsa Terdekat"].map(h => (
                      <th key={h} className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide">{h}</th>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  {stocks.map((s, i) => {
                    const st = stockStatus(s);
                    const exp = nearestExpiry(s);
                    const days = exp ? daysUntil(exp) : null;
                    return (
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
                        <td className="px-4 py-4 text-muted-foreground">{s.minStock ?? DEFAULT_MIN_STOCK} bal</td>
                        <td className="px-4 py-4">
                          <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border ${STOCK_STATUS_STYLE[st]}`}>{st}</span>
                        </td>
                        <td className="px-4 py-4 text-xs">
                          {exp ? (
                            <span className={days !== null && days <= EXPIRY_ALERT_DAYS ? "text-red-600 font-medium" : "text-muted-foreground"}>
                              {fmtDate(exp)}{days !== null ? ` (${days < 0 ? "lewat" : `H-${days}`})` : ""}
                            </span>
                          ) : <span className="text-muted-foreground">—</span>}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
          <p className="text-xs text-muted-foreground mt-3">Stok minimum diatur per produk di menu Produk.</p>
        </>
      )}

      {tab === "kartu" && (
        <>
          <div className="bg-card border border-border rounded-2xl p-5 mb-5">
            <div className="flex flex-wrap gap-4 items-end">
              <div className="flex flex-col gap-1.5">
                <label className="text-xs font-medium">Produk</label>
                <select value={cardProduct} onChange={e => setCardProduct(e.target.value)}
                  className="px-3 py-2 rounded-lg border border-border text-sm focus:ring-2 focus:ring-primary/40 min-w-[220px]">
                  {stocks.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}
                </select>
              </div>
              <div className="flex flex-col gap-1.5">
                <label className="text-xs font-medium">Dari Tanggal</label>
                <input type="date" value={cardStart} onChange={e => setCardStart(e.target.value)} className="px-3 py-2 rounded-lg border border-border text-sm focus:ring-2 focus:ring-primary/40" />
              </div>
              <div className="flex flex-col gap-1.5">
                <label className="text-xs font-medium">Sampai Tanggal</label>
                <input type="date" value={cardEnd} onChange={e => setCardEnd(e.target.value)} className="px-3 py-2 rounded-lg border border-border text-sm focus:ring-2 focus:ring-primary/40" />
              </div>
              {(cardStart || cardEnd) && (
                <button onClick={() => { setCardStart(""); setCardEnd(""); }} className="text-xs text-primary hover:underline cursor-pointer pb-2.5">Reset tanggal</button>
              )}
            </div>
          </div>

          <div className="bg-card border border-border rounded-2xl overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-border bg-muted/30">
                    {["Tanggal", "Jenis", "Jumlah", "Alasan", "Referensi", "Admin", "Saldo"].map(h => (
                      <th key={h} className={`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide ${h === "Saldo" || h === "Jumlah" ? "text-right" : ""}`}>{h}</th>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  {cardRows.length === 0 ? (
                    <tr><td colSpan={7} className="px-4 py-12 text-center text-muted-foreground text-sm">Belum ada mutasi untuk produk dan periode ini.</td></tr>
                  ) : cardRows.map((m, i) => (
                    <tr key={m.id} className={`border-b border-border last:border-0 ${i % 2 === 0 ? "" : "bg-muted/10"}`}>
                      <td className="px-4 py-3 whitespace-nowrap">{fmtDate(m.date)}</td>
                      <td className="px-4 py-3">
                        <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border ${m.type === "Masuk" ? "bg-green-50 text-green-700 border-green-200" : "bg-red-50 text-red-700 border-red-200"}`}>{m.type}</span>
                      </td>
                      <td className={`px-4 py-3 text-right font-semibold ${m.type === "Masuk" ? "text-green-700" : "text-red-700"}`}>{m.type === "Masuk" ? "+" : "-"}{m.qty}</td>
                      <td className="px-4 py-3">
                        <p>{m.reason}</p>
                        {m.note && <p className="text-xs text-muted-foreground">{m.note}</p>}
                      </td>
                      <td className="px-4 py-3 text-xs text-muted-foreground">{m.ref}</td>
                      <td className="px-4 py-3 text-xs text-muted-foreground">{m.admin}</td>
                      <td className="px-4 py-3 text-right font-bold">{m.balance}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
          <p className="text-xs text-muted-foreground mt-3">Riwayat tidak dapat diubah atau dihapus. Jika ada koreksi, catat dengan mutasi baru (Barang Masuk/Keluar).</p>
        </>
      )}

      {modal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4" style={{ backdropFilter: "blur(8px)", backgroundColor: "rgba(44,24,16,0.5)" }} onClick={() => setModal(null)}>
          <div className="bg-card rounded-2xl border border-border shadow-2xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto" onClick={e => e.stopPropagation()}>
            <div className="flex items-center justify-between mb-5">
              <h3 className="font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>{modal === "masuk" ? "Barang Masuk" : "Barang Keluar"}</h3>
              <button onClick={() => setModal(null)} className="p-1.5 rounded-lg hover:bg-muted cursor-pointer"><X className="w-4 h-4" /></button>
            </div>
            <div className="flex flex-col gap-4">
              <Input label={modal === "masuk" ? "Tanggal Masuk" : "Tanggal Keluar"} type="date" value={fDate} onChange={setFDate} required />
              {productSelect}
              <Input label="Jumlah (bal)" type="number" value={fQty} onChange={setFQty} placeholder="0" required />
              {modal === "masuk" ? (
                <>
                  <Select label="Sumber" value={fSource} onChange={setFSource} options={["Produksi", "Retur", "Lainnya"]} />
                  <Input label="Tanggal Kedaluwarsa (opsional)" type="date" value={fExpiry} onChange={setFExpiry} />
                </>
              ) : (
                <Select label="Alasan" value={fReason} onChange={setFReason} options={["Rusak", "Kedaluwarsa", "Sampel", "Lainnya"]} placeholder="Pilih alasan" required />
              )}
              <Input label="Catatan (opsional)" value={fNote} onChange={setFNote} />
              {error && <p className="text-xs text-destructive bg-destructive/10 px-3 py-2 rounded-lg">{error}</p>}
              <div className="flex gap-3 mt-1">
                <Btn variant="outline" className="flex-1" onClick={() => setModal(null)}>Batal</Btn>
                <Btn className="flex-1" onClick={modal === "masuk" ? submitMasuk : submitKeluar}>Simpan</Btn>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

// ─── App Root ─────────────────────────────────────────────────────────────────
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
  const [movements, setMovements] = useState<StockMovement[]>(() => {
    try {
      const raw = localStorage.getItem("lintang_stock_moves");
      if (raw) return JSON.parse(raw);
    } catch { /* abaikan */ }
    return openingMovements(stocks);
  });
  const stocksRef = useRef(stocks);
  stocksRef.current = stocks;
  const recordMovements = useCallback((list: MovementInput[]) => {
    const res = applyMovements(stocksRef.current, list);
    stocksRef.current = res.stocks;
    setStocks(res.stocks);
    setMovements(prev => [...prev, ...res.movements]);
  }, []);
  const [offlineTrx, setOfflineTrx] = useState<OfflineTransaction[]>(() => {
    try { return JSON.parse(localStorage.getItem("lintang_trx") || "[]"); } catch { return []; }
  });

  useEffect(() => {
    localStorage.setItem("lintang_cart", JSON.stringify(cart));
  }, [cart]);

  useEffect(() => { localStorage.setItem("lintang_orders", JSON.stringify(orders)); }, [orders]);
  useEffect(() => { localStorage.setItem("lintang_stocks_v2", JSON.stringify(stocks)); }, [stocks]);
  useEffect(() => { localStorage.setItem("lintang_trx", JSON.stringify(offlineTrx)); }, [offlineTrx]);
  useEffect(() => { localStorage.setItem("lintang_stock_moves", JSON.stringify(movements)); }, [movements]);
  const activeTrx = offlineTrx.filter(t => t.status !== "VOID");

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
    const fresh = PRODUCTS.map((p, i) => ({ ...p, stock: [100, 100, 100, 100, 100][i] }));
    setStocks(fresh);
    setMovements(openingMovements(fresh));
    ["lintang_cart","lintang_orders","lintang_stocks_v2","lintang_trx","lintang_stock_moves"].forEach(k => localStorage.removeItem(k));
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
          {page === "admin-dashboard" && <AdminDashboard setPage={setPage} orders={orders} trx={activeTrx} stocks={stocks} onReset={resetData} />}
          {page === "admin-orders" && <AdminOrders orders={orders} setOrders={setOrders} />}
          {page === "admin-transactions" && <AdminTransactions trx={offlineTrx} setTrx={setOfflineTrx} stocks={stocks} recordMovements={recordMovements} />}
          {page === "admin-products" && <AdminProducts products={products} setProducts={setProducts} stocks={stocks} setStocks={setStocks} />}
          {page === "admin-stock" && <AdminStock stocks={stocks} movements={movements} recordMovements={recordMovements} />}
          {page === "admin-reports" && <AdminReports orders={orders} trx={activeTrx} movements={movements} />}
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
