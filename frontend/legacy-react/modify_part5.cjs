const fs = require('fs');
const file = 'src/app/App.tsx';
let code = fs.readFileSync(file, 'utf8');

function replaceExact(search, replace) {
  if (!code.includes(search)) {
    console.error("COULD NOT FIND:\n" + search.substring(0, 100) + "...");
  } else {
    code = code.replace(search, replace);
  }
}

// 1. Transaksi Offline (AdminTransactions & AdminReports)
// AdminTransactions header
replaceExact(
  `{["Tanggal", "Pembeli", "Produk", "Bayar", "Total", "Catatan"].map(h => (
                  <th key={h} className={\`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide \${h === 'Total' ? 'text-right' : ''}\`}>{h}</th>
                ))}`,
  `{["Tanggal", "Produk", "Bayar", "Total", "Catatan"].map(h => (
                  <th key={h} className={\`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide \${h === 'Total' ? 'text-right' : ''}\`}>{h}</th>
                ))}`
);
// AdminTransactions row
replaceExact(
  '<td className="px-4 py-3 font-medium">{t.buyerName}</td>',
  ''
);

// AdminReports header
replaceExact(
  `{["Tanggal","Pembeli","Total","Catatan"].map(h => (
                  <th key={h} className={\`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide \${h === 'Total' ? 'text-right' : ''}\`}>{h}</th>
                ))}`,
  `{["Tanggal","Total","Catatan"].map(h => (
                  <th key={h} className={\`text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide \${h === 'Total' ? 'text-right' : ''}\`}>{h}</th>
                ))}`
);
// AdminReports row
replaceExact(
  '<td className="px-4 py-3 font-medium">{t.buyerName || "-"}</td>',
  ''
);


// 2. Kategori Penjualan -> Keduanya
// Product Interface
replaceExact(
  'salesChannel?: "online" | "offline";',
  'salesChannel?: "online" | "offline" | "both";'
);
// AdminProducts state
replaceExact(
  'const [salesChannel, setSalesChannel] = useState<"online" | "offline">("online");',
  'const [salesChannel, setSalesChannel] = useState<"online" | "offline" | "both">("online");'
);
// AdminProducts JSX
replaceExact(
  `<label className="flex items-center gap-2 cursor-pointer">
                    <input type="radio" checked={salesChannel === "offline"} onChange={() => setSalesChannel("offline")} className="w-4 h-4 text-primary" />
                    <span className="text-sm">Offline (Toko Fisik)</span>
                  </label>
                </div>`,
  `<label className="flex items-center gap-2 cursor-pointer">
                    <input type="radio" checked={salesChannel === "offline"} onChange={() => setSalesChannel("offline")} className="w-4 h-4 text-primary" />
                    <span className="text-sm">Offline (Toko Fisik)</span>
                  </label>
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input type="radio" checked={salesChannel === "both"} onChange={() => setSalesChannel("both")} className="w-4 h-4 text-primary" />
                    <span className="text-sm">Keduanya</span>
                  </label>
                </div>`
);
// AdminProducts logic offline -> offline || both
replaceExact(
  'if (salesChannel === "offline" && !stocks.some(s => s.id === editing.id)) {',
  'if ((salesChannel === "offline" || salesChannel === "both") && !stocks.some(s => s.id === editing.id)) {'
);
replaceExact(
  'if (salesChannel === "offline") {',
  'if (salesChannel === "offline" || salesChannel === "both") {'
);

// 3. Hapus sub-judul Stok produk toko offline
replaceExact(
  '<p className="text-sm text-muted-foreground mt-0.5">Stok produk toko offline</p>',
  ''
);

// 4. Hapus kolom STATUS dari tabel Stok Offline
replaceExact(
  `{["Produk", "Stok Saat Ini", "Status", "Penyesuaian", "Aksi"].map(h => (
                  <th key={h} className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide">{h}</th>
                ))}`,
  `{["Produk", "Stok Saat Ini", "Aksi"].map(h => (
                  <th key={h} className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide">{h}</th>
                ))}`
);
const statusTdRegex = /<td className="px-4 py-4">\s*\{s\.stock < 50 \? \([\s\S]*?<\/Check> Cukup\s*<\/span>\s*\)\}\s*<\/td>/;
code = code.replace(statusTdRegex, '');

// 5. Perombakan Kolom Penyesuaian & Aksi
const adjAksiRegex = /<td className="px-4 py-4">\s*<div className="flex items-center gap-2">\s*<button onClick=\{\(\) => \{ const v = Math\.max\(0, \(parseInt\(adjustments\[s\.id\] \|\| "0"\) \|\| 0\) - 1\); updateAdj\(s\.id, v === 0 \? "" : String\(v\)\); \}\}[\s\S]*?<\/Plus>\s*<\/button>\s*<\/div>\s*<\/td>\s*<td className="px-4 py-4">\s*<button onClick=\{\(\) => applyAdd\(s\.id\)\}[\s\S]*?Tersimpan<\/>[^<]*?: "\+ Tambah Stok"\}\s*<\/button>\s*<\/td>/;

const newAksi = `<td className="px-4 py-4">
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
                      <button onClick={() => applyAdd(s.id)} title="Simpan perubahan stok"
                        className={\`w-7 h-7 rounded-lg border flex items-center justify-center transition-colors cursor-pointer \${saved === s.id ? "bg-green-100 text-green-700 border-green-200" : "bg-primary/10 text-primary border-primary/20 hover:bg-primary/20"}\`}>
                        <Check className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>`;

code = code.replace(adjAksiRegex, newAksi);

fs.writeFileSync(file, code);
console.log("Modifications 5 done.");
