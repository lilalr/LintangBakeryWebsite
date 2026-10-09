import React from "react";

export type Page =
  | "home" | "auth" | "email-verify"
  | "catalog" | "product-detail" | "cart"
  | "checkout" | "checkout-success"
  | "buyer-dashboard" | "buyer-profile"
  | "admin-dashboard" | "admin-orders" | "admin-order-detail"
  | "admin-products" | "admin-stock"
  | "admin-transactions" | "admin-reports";

export interface OfflineTransaction {
  id: string;
  date: string;
  buyerName: string;
  items: { product: Product; quantity: number; subtotal: number }[];
  total: number;
  paymentMethod: "cash" | "qris-tf";
  notes: string;
}

export interface StockItem extends Product {
  stock: number;
}

export type UserRole = "buyer" | "admin" | null;

export interface Product {
  id: number;
  name: string;
  description: string;
  pricePerBal: number;
  image: string;
  category: string;
  salesChannel?: "online" | "offline" | "both";
}

export interface OrderItem {
  product: Product;
  quantity: number;
  subtotal: number;
}

export interface Order {
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

export type OrderStatus =
  | "Menunggu Konfirmasi"
  | "Pembayaran DP Disetujui"
  | "Sedang Diproduksi"
  | "Siap Dikirim"
  | "Sedang Dikirim"
  | "Sudah Sampai"
  | "Selesai/Lunas";

// ─── Data ─────────────────────────────────────────────────────────────────────
export const PRODUCTS: Product[] = [
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

export const PROVINCES: Record<string, string[]> = {
  "DI Yogyakarta": ["Kota Yogyakarta", "Sleman", "Bantul", "Kulonprogo", "Gunungkidul"],
  "Jawa Tengah": ["Kota Semarang", "Kota Solo", "Kota Magelang", "Cilacap", "Purwokerto", "Klaten", "Wonosobo"],
  "Jawa Barat": ["Kota Bandung", "Kota Bogor", "Bekasi", "Depok", "Cirebon", "Kab. Tasikmalaya", "Kota Tasikmalaya"],
  "Jawa Timur": ["Kota Surabaya", "Kota Malang", "Sidoarjo", "Gresik", "Kediri", "Jember"],
  "DKI Jakarta": ["Jakarta Pusat", "Jakarta Selatan", "Jakarta Utara", "Jakarta Barat", "Jakarta Timur"],
  "Banten": ["Kota Tangerang", "Kota Serang", "Lebak", "Pandeglang"],
  "Sumatera Utara": ["Kota Medan", "Deli Serdang", "Karo", "Serdang Bedagai"],
  "Sulawesi Selatan": ["Kota Makassar", "Gowa", "Maros", "Bone"],
};

export const ORDER_STATUSES: OrderStatus[] = [
  "Menunggu Konfirmasi",
  "Pembayaran DP Disetujui",
  "Sedang Diproduksi",
  "Siap Dikirim",
  "Sedang Dikirim",
  "Sudah Sampai",
  "Selesai/Lunas",
];

export const MOCK_ORDERS: Order[] = [
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

export const MONTHLY_DATA = [
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
export const fmt = (n: number) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(n);

export const fmtDate = (d: string) => {
  if (!d) return "-";
  return d.split("-").reverse().join("/");
};

export const statusColor: Record<OrderStatus, string> = {
  "Menunggu Konfirmasi": "bg-amber-100 text-amber-800 border-amber-200",
  "Pembayaran DP Disetujui": "bg-blue-100 text-blue-800 border-blue-200",
  "Sedang Diproduksi": "bg-purple-100 text-purple-800 border-purple-200",
  "Siap Dikirim": "bg-cyan-100 text-cyan-800 border-cyan-200",
  "Sedang Dikirim": "bg-orange-100 text-orange-800 border-orange-200",
  "Sudah Sampai": "bg-teal-100 text-teal-800 border-teal-200",
  "Selesai/Lunas": "bg-green-100 text-green-800 border-green-200",
};

// ─── Shared Components ────────────────────────────────────────────────────────
export function Btn({
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

export function StatusBadge({ status }: { status: OrderStatus }) {
  return (
    <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border ${statusColor[status]}`}>
      {status}
    </span>
  );
}

export function Input({ label, type = "text", value, onChange, placeholder, required, error }: {
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

export function Select({ label, value, onChange, options, placeholder, required }: {
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
