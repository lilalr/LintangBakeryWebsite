const fs = require('fs');
const file = 'src/app/App.tsx';
let code = fs.readFileSync(file, 'utf8');

// 1. Add fmtDate helper
const fmtRegex = /const fmt = \(n: number\) =>\n  new Intl\.NumberFormat\("id-ID", \{ style: "currency", currency: "IDR", maximumFractionDigits: 0 \}\)\.format\(n\);/;
if (code.includes('const fmtDate')) {
  console.log("fmtDate already exists.");
} else {
  code = code.replace(fmtRegex, `const fmt = (n: number) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(n);

const fmtDate = (d: string) => {
  if (!d) return "-";
  return d.split("-").reverse().join("/");
};`);
}

// 2. Replace AdminReports component entirely
const reportsRegex = /\/\/ ─── Page: Admin Reports ───────────────────────────────────────────────────────\nfunction AdminReports\([\s\S]*?\n\}\n\n\/\/ ─── Page: Admin Products ─────────────────────────────────────────────────────/;
const newReportsCode = `// ─── Page: Admin Reports ───────────────────────────────────────────────────────
function AdminReports({ orders, trx }: { orders: Order[]; trx: OfflineTransaction[] }) {
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
    return \`\${names[parseInt(mo) - 1]} \${y}\`;
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
            \`\${fmtDate(filterStart)} - \${fmtDate(filterEnd)}\`
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
          { label: "Pendapatan Online", value: fmt(totalOnline), sub: \`\${filteredOrders.length} pesanan\`, color: "text-blue-600 bg-blue-50" },
          { label: "Pendapatan Offline", value: fmt(totalOffline), sub: \`\${filteredTrx.length} transaksi\`, color: "text-primary bg-primary/10" },
          { label: "Produk Terlaris", value: topProduct ? topProduct.name.split(" ").slice(0, 2).join(" ") : "-", sub: topProduct ? \`\${topProduct.total} bal\` : "Belum ada data", color: "text-amber-600 bg-amber-50" },
        ].map(c => (
          <div key={c.label} className="bg-card border border-border rounded-2xl p-5">
            <div className={\`w-10 h-10 rounded-xl flex items-center justify-center mb-3 \${c.color} print:border print:border-border\`}>
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
              <Tooltip formatter={(v) => [\`\${v} bal\`, 'Terjual']} />
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
                  <th key={h} className={\`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide \${h === 'Total' ? 'text-right' : ''}\`}>{h}</th>
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
                {["Tanggal","Pembeli","Total","Catatan"].map(h => (
                  <th key={h} className={\`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide \${h === 'Total' ? 'text-right' : ''}\`}>{h}</th>
                ))}
              </tr></thead>
              <tbody>
                {filteredTrx.length === 0
                  ? <tr><td colSpan={4} className="px-4 py-8 text-center text-muted-foreground text-sm">Tidak ada transaksi offline.</td></tr>
                  : filteredTrx.map(t => (
                    <tr key={t.id} className="border-b border-border last:border-0 hover:bg-muted/20">
                      <td className="px-4 py-3 whitespace-nowrap">{fmtDate(t.date)}</td>
                      <td className="px-4 py-3 font-medium">{t.buyerName || "-"}</td>
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

// ─── Page: Admin Products ─────────────────────────────────────────────────────`;

code = code.replace(reportsRegex, newReportsCode);

// 3. Fix date formats in other places
// Admin Orders Table:
code = code.replace(
  /<td className="px-4 py-3">\s*<p className="font-medium text-foreground">\{o\.buyer\}<\/p>\s*<p className="text-xs text-muted-foreground">\{o\.date\}<\/p>\s*<\/td>/g,
  '<td className="px-4 py-3">\n                    <p className="font-medium text-foreground">{o.buyer}</p>\n                    <p className="text-xs text-muted-foreground">{fmtDate(o.date)}</p>\n                  </td>'
);
// Admin Order Detail:
code = code.replace(
  /<div><span className="text-muted-foreground block text-xs mb-0\.5">Tanggal Pesan<\/span><span className="font-medium">\{detail\.date\}<\/span><\/div>/g,
  '<div><span className="text-muted-foreground block text-xs mb-0.5">Tanggal Pesan</span><span className="font-medium">{fmtDate(detail.date)}</span></div>'
);
// Buyer Order List:
code = code.replace(
  /<span className="text-muted-foreground text-sm ml-3">\{order\.date\}<\/span>/g,
  '<span className="text-muted-foreground text-sm ml-3">{fmtDate(order.date)}</span>'
);
// Buyer Order Detail:
code = code.replace(
  /<p className="text-sm text-muted-foreground mb-6">\{selectedOrder\.date\}<\/p>/g,
  '<p className="text-sm text-muted-foreground mb-6">{fmtDate(selectedOrder.date)}</p>'
);
// Admin Notifications:
code = code.replace(
  /<p className="text-xs text-muted-foreground mt-1">\{n\.date\}<\/p>/g,
  '<p className="text-xs text-muted-foreground mt-1">{fmtDate(n.date)}</p>'
);
// Admin Transactions table (Kasir):
code = code.replace(
  /<td className="px-4 py-3 text-sm">\{t\.date\}<\/td>/g,
  '<td className="px-4 py-3 text-sm whitespace-nowrap">{fmtDate(t.date)}</td>'
);
// Add right-align to total in Kasir Table
code = code.replace(
  /<td className="px-4 py-3 font-semibold text-primary">\{fmt\(t\.total\)\}<\/td>/g,
  '<td className="px-4 py-3 font-semibold text-primary text-right">{fmt(t.total)}</td>'
);
// Add right-align to total column header in Kasir Table
code = code.replace(
  /\{\["Tanggal", "Pembeli", "Produk", "Bayar", "Total", "Catatan"\]\.map\(h => \(\n                  <th key=\{h\} className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide">\{h\}<\/th>\n                \)\)\}/g,
  `{["Tanggal", "Pembeli", "Produk", "Bayar", "Total", "Catatan"].map(h => (
                  <th key={h} className={\`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide \${h === 'Total' ? 'text-right' : ''}\`}>{h}</th>
                ))}`
);

// Add right-align to total in Admin Orders Table
code = code.replace(
  /<td className="px-4 py-3 font-semibold text-primary">\{fmt\(o\.total \+ o\.shippingCost\)\}<\/td>/g,
  '<td className="px-4 py-3 font-semibold text-primary text-right">{fmt(o.total + o.shippingCost)}</td>'
);
code = code.replace(
  /\{\["No\. Pesanan", "Pembeli", "Produk", "Total", "Status", "Aksi"\]\.map\(h => \(\n                  <th key=\{h\} className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide">\{h\}<\/th>\n                \)\)\}/g,
  `{["No. Pesanan", "Pembeli", "Produk", "Total", "Status", "Aksi"].map(h => (
                  <th key={h} className={\`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide \${h === 'Total' ? 'text-right' : ''}\`}>{h}</th>
                ))}`
);


fs.writeFileSync(file, code);
console.log("Modifications 3 & 4 done.");
