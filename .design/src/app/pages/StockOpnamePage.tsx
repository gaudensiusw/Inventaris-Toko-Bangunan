import React, { useState, useMemo } from 'react';
import { useInventory } from '../contexts/InventoryContext';
import { useAuth } from '../contexts/AuthContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import { Input } from '../components/ui/input';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '../components/ui/dialog';
import { Label } from '../components/ui/label';
import { Textarea } from '../components/ui/textarea';
import { toast } from 'sonner';
import {
  ClipboardCheck,
  Search,
  FileText,
  AlertTriangle,
  CheckCircle2,
  TrendingUp,
  TrendingDown,
  Minus,
  Save,
  X,
  Filter,
  Download,
} from 'lucide-react';
import { cn } from '../components/ui/utils';

export const StockOpnamePage: React.FC = () => {
  const { user } = useAuth();
  const { products, categories } = useInventory();
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [showVarianceOnly, setShowVarianceOnly] = useState(false);
  const [opnameItems, setOpnameItems] = useState<Map<string, {
    physical_stock: number;
    reason: string;
    notes: string;
  }>>(new Map());
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Filter products
  const filteredProducts = useMemo(() => {
    let filtered = products;

    if (searchQuery) {
      filtered = filtered.filter(p =>
        p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        p.sku.toLowerCase().includes(searchQuery.toLowerCase()) ||
        p.barcode?.toLowerCase().includes(searchQuery.toLowerCase())
      );
    }

    if (selectedCategory !== 'all') {
      filtered = filtered.filter(p => p.category_id === selectedCategory);
    }

    return filtered.map(product => {
      const opnameData = opnameItems.get(product.id);
      const physical_stock = opnameData?.physical_stock ?? product.current_stock;
      const variance = physical_stock - product.current_stock;
      const variance_percentage = product.current_stock > 0 
        ? (variance / product.current_stock) * 100 
        : 0;
      const variance_value = variance * product.hpp;
      const variance_type: 'match' | 'surplus' | 'shortage' = 
        variance === 0 ? 'match' : variance > 0 ? 'surplus' : 'shortage';

      return {
        ...product,
        physical_stock,
        variance,
        variance_percentage,
        variance_value,
        variance_type,
        reason: opnameData?.reason || '',
        notes: opnameData?.notes || '',
      };
    });
  }, [products, searchQuery, selectedCategory, opnameItems]);

  // Filter variance only
  const displayedProducts = useMemo(() => {
    if (showVarianceOnly) {
      return filteredProducts.filter(p => p.variance !== 0);
    }
    return filteredProducts;
  }, [filteredProducts, showVarianceOnly]);

  // Summary statistics
  const summary = useMemo(() => {
    const total_items = filteredProducts.length;
    const counted_items = filteredProducts.filter(p => 
      opnameItems.has(p.id)
    ).length;
    const match_items = filteredProducts.filter(p => p.variance === 0).length;
    const surplus_items = filteredProducts.filter(p => p.variance > 0).length;
    const shortage_items = filteredProducts.filter(p => p.variance < 0).length;
    const total_variance_value = filteredProducts.reduce((sum, p) => 
      sum + p.variance_value, 0
    );

    return {
      total_items,
      counted_items,
      match_items,
      surplus_items,
      shortage_items,
      total_variance_value,
      progress: total_items > 0 ? (counted_items / total_items) * 100 : 0,
    };
  }, [filteredProducts, opnameItems]);

  const handlePhysicalStockChange = (productId: string, value: string) => {
    const numValue = parseFloat(value) || 0;
    const current = opnameItems.get(productId) || { physical_stock: 0, reason: '', notes: '' };
    const updated = new Map(opnameItems);
    updated.set(productId, { ...current, physical_stock: numValue });
    setOpnameItems(updated);
  };

  const handleReasonChange = (productId: string, value: string) => {
    const current = opnameItems.get(productId) || { physical_stock: 0, reason: '', notes: '' };
    const updated = new Map(opnameItems);
    updated.set(productId, { ...current, reason: value });
    setOpnameItems(updated);
  };

  const handleNotesChange = (productId: string, value: string) => {
    const current = opnameItems.get(productId) || { physical_stock: 0, reason: '', notes: '' };
    const updated = new Map(opnameItems);
    updated.set(productId, { ...current, notes: value });
    setOpnameItems(updated);
  };

  const handleCopySystemStock = (productId: string) => {
    const product = products.find(p => p.id === productId);
    if (product) {
      handlePhysicalStockChange(productId, product.current_stock.toString());
      toast.success('Stok sistem disalin ke stok fisik');
    }
  };

  const handleResetProduct = (productId: string) => {
    const updated = new Map(opnameItems);
    updated.delete(productId);
    setOpnameItems(updated);
    toast.success('Data produk direset');
  };

  const handleSubmitOpname = async () => {
    if (summary.counted_items === 0) {
      toast.error('Belum ada produk yang dihitung');
      return;
    }

    // Check if variance items have reasons
    const varianceItems = filteredProducts.filter(p => 
      p.variance !== 0 && opnameItems.has(p.id)
    );
    const missingReasons = varianceItems.filter(p => !p.reason);
    
    if (missingReasons.length > 0) {
      toast.error(`${missingReasons.length} produk dengan selisih belum memiliki alasan`);
      return;
    }

    setIsSubmitting(true);

    // Simulate API call
    setTimeout(() => {
      toast.success('Stock Opname berhasil disimpan');
      setIsSubmitting(false);
      // In real app, would update inventory context and navigate to history
    }, 1500);
  };

  const handleExportToCSV = () => {
    const csvData = displayedProducts.map(p => ({
      SKU: p.sku,
      Nama: p.name,
      'Stok Sistem': p.current_stock,
      'Stok Fisik': p.physical_stock,
      Selisih: p.variance,
      'Selisih %': p.variance_percentage.toFixed(2),
      'Nilai Selisih': p.variance_value,
      Alasan: p.reason || '-',
    }));

    const headers = Object.keys(csvData[0]);
    const csvContent = [
      headers.join(','),
      ...csvData.map(row => headers.map(h => row[h as keyof typeof row]).join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `stock-opname-${new Date().toISOString().split('T')[0]}.csv`;
    a.click();

    toast.success('Data diekspor ke CSV');
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const getVarianceBadge = (type: 'match' | 'surplus' | 'shortage') => {
    switch (type) {
      case 'match':
        return (
          <Badge className="bg-green-100 text-green-700">
            <CheckCircle2 className="w-3 h-3 mr-1" />
            Sesuai
          </Badge>
        );
      case 'surplus':
        return (
          <Badge className="bg-blue-100 text-blue-700">
            <TrendingUp className="w-3 h-3 mr-1" />
            Lebih
          </Badge>
        );
      case 'shortage':
        return (
          <Badge className="bg-red-100 text-red-700">
            <TrendingDown className="w-3 h-3 mr-1" />
            Kurang
          </Badge>
        );
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">Stock Opname</h1>
          <p className="text-slate-600 mt-1">
            Bandingkan stok fisik dengan stok sistem untuk akurasi inventory
          </p>
        </div>
        <div className="flex items-center gap-2">
          <Button variant="outline" onClick={handleExportToCSV}>
            <Download className="w-4 h-4 mr-2" />
            Export CSV
          </Button>
        </div>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card className="p-4">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <ClipboardCheck className="w-6 h-6 text-blue-600" />
            </div>
            <div className="flex-1">
              <p className="text-sm text-slate-600">Progress</p>
              <div className="flex items-baseline gap-2">
                <p className="text-xl font-bold text-slate-900">
                  {summary.counted_items}/{summary.total_items}
                </p>
                <p className="text-sm text-slate-500">
                  ({summary.progress.toFixed(0)}%)
                </p>
              </div>
            </div>
          </div>
        </Card>

        <Card className="p-4">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <CheckCircle2 className="w-6 h-6 text-green-600" />
            </div>
            <div className="flex-1">
              <p className="text-sm text-slate-600">Sesuai</p>
              <div className="flex items-baseline gap-2">
                <p className="text-xl font-bold text-green-900">{summary.match_items}</p>
                <p className="text-sm text-slate-500">produk</p>
              </div>
            </div>
          </div>
        </Card>

        <Card className="p-4">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
              <AlertTriangle className="w-6 h-6 text-red-600" />
            </div>
            <div className="flex-1">
              <p className="text-sm text-slate-600">Selisih</p>
              <div className="flex items-baseline gap-2">
                <p className="text-xl font-bold text-red-900">
                  {summary.surplus_items + summary.shortage_items}
                </p>
                <p className="text-sm text-slate-500">produk</p>
              </div>
            </div>
          </div>
        </Card>

        <Card className="p-4">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <TrendingUp className="w-6 h-6 text-blue-600" />
            </div>
            <div className="flex-1">
              <p className="text-sm text-slate-600">Surplus</p>
              <p className="text-xl font-bold text-blue-900">{summary.surplus_items}</p>
            </div>
          </div>
        </Card>

        <Card className="p-4">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
              <TrendingDown className="w-6 h-6 text-orange-600" />
            </div>
            <div className="flex-1">
              <p className="text-sm text-slate-600">Kekurangan</p>
              <p className="text-xl font-bold text-orange-900">{summary.shortage_items}</p>
            </div>
          </div>
        </Card>

        <Card className="p-4">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <FileText className="w-6 h-6 text-purple-600" />
            </div>
            <div className="flex-1">
              <p className="text-sm text-slate-600">Total Nilai Selisih</p>
              <p className={cn(
                "text-lg font-bold",
                summary.total_variance_value > 0 ? "text-blue-900" : "text-red-900"
              )}>
                {formatCurrency(Math.abs(summary.total_variance_value))}
              </p>
            </div>
          </div>
        </Card>
      </div>

      {/* Filters and Actions */}
      <Card className="p-4">
        <div className="flex flex-col sm:flex-row gap-4">
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <Input
              placeholder="Cari produk (nama, SKU, barcode)..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-10"
            />
          </div>
          <select
            value={selectedCategory}
            onChange={(e) => setSelectedCategory(e.target.value)}
            className="px-3 py-2 border rounded-lg text-sm min-w-[200px]"
          >
            <option value="all">Semua Kategori</option>
            {categories.map(cat => (
              <option key={cat.id} value={cat.id}>{cat.name}</option>
            ))}
          </select>
          <Button
            variant={showVarianceOnly ? "default" : "outline"}
            onClick={() => setShowVarianceOnly(!showVarianceOnly)}
          >
            <Filter className="w-4 h-4 mr-2" />
            {showVarianceOnly ? 'Tampilkan Semua' : 'Hanya Selisih'}
          </Button>
        </div>
      </Card>

      {/* Products Table */}
      <Card className="p-6">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b">
                <th className="text-left py-3 px-4 font-semibold text-slate-700">Produk</th>
                <th className="text-center py-3 px-4 font-semibold text-slate-700">Stok Sistem</th>
                <th className="text-center py-3 px-4 font-semibold text-slate-700">Stok Fisik</th>
                <th className="text-center py-3 px-4 font-semibold text-slate-700">Selisih</th>
                <th className="text-right py-3 px-4 font-semibold text-slate-700">Nilai Selisih</th>
                <th className="text-center py-3 px-4 font-semibold text-slate-700">Status</th>
                <th className="text-center py-3 px-4 font-semibold text-slate-700">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {displayedProducts.map((product) => (
                <tr key={product.id} className="border-b hover:bg-slate-50">
                  <td className="py-3 px-4">
                    <div>
                      <p className="font-medium text-slate-900">{product.name}</p>
                      <p className="text-sm text-slate-500">
                        SKU: {product.sku} | {product.base_unit}
                      </p>
                    </div>
                  </td>
                  <td className="py-3 px-4 text-center">
                    <p className="font-medium text-slate-900">{product.current_stock}</p>
                  </td>
                  <td className="py-3 px-4">
                    <div className="flex items-center gap-2 justify-center">
                      <Input
                        type="number"
                        value={product.physical_stock}
                        onChange={(e) => handlePhysicalStockChange(product.id, e.target.value)}
                        className="w-24 text-center"
                        min="0"
                        step="0.01"
                      />
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleCopySystemStock(product.id)}
                        title="Salin stok sistem"
                      >
                        <Minus className="w-4 h-4" />
                      </Button>
                    </div>
                  </td>
                  <td className="py-3 px-4">
                    <div className="text-center">
                      <p className={cn(
                        "font-bold",
                        product.variance > 0 && "text-blue-700",
                        product.variance < 0 && "text-red-700",
                        product.variance === 0 && "text-green-700"
                      )}>
                        {product.variance > 0 ? '+' : ''}{product.variance}
                      </p>
                      {product.variance !== 0 && (
                        <p className="text-xs text-slate-500">
                          ({product.variance_percentage > 0 ? '+' : ''}
                          {product.variance_percentage.toFixed(1)}%)
                        </p>
                      )}
                    </div>
                  </td>
                  <td className="py-3 px-4 text-right">
                    <p className={cn(
                      "font-medium",
                      product.variance_value > 0 && "text-blue-700",
                      product.variance_value < 0 && "text-red-700"
                    )}>
                      {formatCurrency(Math.abs(product.variance_value))}
                    </p>
                  </td>
                  <td className="py-3 px-4 text-center">
                    {getVarianceBadge(product.variance_type)}
                  </td>
                  <td className="py-3 px-4">
                    <div className="flex items-center justify-center gap-2">
                      {product.variance !== 0 && (
                        <Dialog>
                          <DialogTrigger asChild>
                            <Button variant="outline" size="sm">
                              {product.reason ? 'Edit' : 'Alasan'}
                            </Button>
                          </DialogTrigger>
                          <DialogContent>
                            <DialogHeader>
                              <DialogTitle>Alasan Selisih</DialogTitle>
                              <DialogDescription>
                                {product.name} - SKU: {product.sku}
                              </DialogDescription>
                            </DialogHeader>
                            <div className="space-y-4 pt-4">
                              <div className="grid grid-cols-3 gap-4 p-4 bg-slate-50 rounded-lg">
                                <div className="text-center">
                                  <p className="text-sm text-slate-600">Stok Sistem</p>
                                  <p className="text-lg font-bold">{product.current_stock}</p>
                                </div>
                                <div className="text-center">
                                  <p className="text-sm text-slate-600">Stok Fisik</p>
                                  <p className="text-lg font-bold">{product.physical_stock}</p>
                                </div>
                                <div className="text-center">
                                  <p className="text-sm text-slate-600">Selisih</p>
                                  <p className={cn(
                                    "text-lg font-bold",
                                    product.variance > 0 ? "text-blue-700" : "text-red-700"
                                  )}>
                                    {product.variance > 0 ? '+' : ''}{product.variance}
                                  </p>
                                </div>
                              </div>

                              <div>
                                <Label htmlFor="reason">Alasan Selisih *</Label>
                                <select
                                  id="reason"
                                  value={product.reason}
                                  onChange={(e) => handleReasonChange(product.id, e.target.value)}
                                  className="w-full px-3 py-2 border rounded-lg mt-1"
                                >
                                  <option value="">Pilih alasan...</option>
                                  <option value="Kesalahan Input">Kesalahan Input</option>
                                  <option value="Barang Rusak/Hilang">Barang Rusak/Hilang</option>
                                  <option value="Kesalahan Hitung">Kesalahan Hitung</option>
                                  <option value="Pencurian">Pencurian</option>
                                  <option value="Expired/Kadaluarsa">Expired/Kadaluarsa</option>
                                  <option value="Selisih Normal">Selisih Normal</option>
                                  <option value="Lainnya">Lainnya</option>
                                </select>
                              </div>

                              <div>
                                <Label htmlFor="notes">Catatan Tambahan</Label>
                                <Textarea
                                  id="notes"
                                  value={product.notes}
                                  onChange={(e) => handleNotesChange(product.id, e.target.value)}
                                  placeholder="Tambahkan catatan jika diperlukan..."
                                  className="mt-1"
                                  rows={3}
                                />
                              </div>
                            </div>
                          </DialogContent>
                        </Dialog>
                      )}
                      {opnameItems.has(product.id) && (
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => handleResetProduct(product.id)}
                          title="Reset data"
                        >
                          <X className="w-4 h-4" />
                        </Button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {displayedProducts.length === 0 && (
            <div className="text-center py-12">
              <ClipboardCheck className="w-12 h-12 text-slate-300 mx-auto mb-3" />
              <p className="text-slate-600">
                {showVarianceOnly 
                  ? 'Tidak ada produk dengan selisih' 
                  : 'Tidak ada produk ditemukan'
                }
              </p>
            </div>
          )}
        </div>
      </Card>

      {/* Submit Actions */}
      {summary.counted_items > 0 && (
        <Card className="p-6">
          <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
              <h3 className="font-semibold text-slate-900">
                Siap submit stock opname?
              </h3>
              <p className="text-sm text-slate-600 mt-1">
                {summary.counted_items} produk telah dihitung, {summary.surplus_items + summary.shortage_items} produk memiliki selisih
              </p>
            </div>
            <div className="flex gap-2">
              <Button
                variant="outline"
                onClick={() => {
                  setOpnameItems(new Map());
                  toast.success('Data direset');
                }}
              >
                Reset Semua
              </Button>
              <Button
                onClick={handleSubmitOpname}
                disabled={isSubmitting}
                className="bg-blue-600 hover:bg-blue-700"
              >
                <Save className="w-4 h-4 mr-2" />
                {isSubmitting ? 'Menyimpan...' : 'Submit Opname'}
              </Button>
            </div>
          </div>
        </Card>
      )}
    </div>
  );
};
