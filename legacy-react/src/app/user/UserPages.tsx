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

export function Navbar({ page, setPage, cartCount, isLoggedIn, userRole, onLogout }: {
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
export function Footer({ setPage }: { setPage: (p: Page) => void }) {
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
export function HomePage({ products, setPage, setSelectedProduct, addToCart, isLoggedIn }: { products: Product[];
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
export function AuthPage({ setPage, setIsLoggedIn, setUserRole }: {
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
export function EmailVerifyPage({ setPage, setIsLoggedIn, setUserRole }: {
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
export function CatalogPage({ products, setPage, setSelectedProduct, addToCart, isLoggedIn }: { products: Product[];
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
export function ProductDetailPage({ product, setPage, addToCart, isLoggedIn, setCheckoutCart }: {
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
export function AddToCartModal({ product, onClose, onConfirm }: {
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
export function QrisModal() {
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
export function CartPage({ cart, setPage, removeFromCart, updateCartQty, onCheckout }: {
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
export function CheckoutPage({ setPage, cart, removeCheckedOutItems, addOrder }: {
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
export function ProfileCard() {
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

export function BuyerDashboard({ setPage, orders }: { setPage: (p: Page) => void; orders: Order[] }) {
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
