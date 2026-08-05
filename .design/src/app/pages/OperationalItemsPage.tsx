import React, { useState, useMemo } from 'react';
import { useInventory } from '../contexts/InventoryContext';
import { useAuth } from '../contexts/AuthContext';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Badge } from '../components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogFooter } from '../components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../components/ui/table';
import { toast } from 'sonner';
import {
  Package,
  Plus,
  Edit,
  Trash2,
  Search,
  ShoppingCart,
  TrendingDown,
  AlertTriangle,
  PackageOpen,
  Clipboard,
  Sparkles,
  DollarSign,
} from 'lucide-react';
import { OperationalItem, OperationalExpense } from '../data/mockData';

export const OperationalItemsPage: React.FC = () => {
  const { operationalItems, operationalExpenses, addOperationalItem, updateOperationalItem, deleteOperationalItem, addOperationalExpense } = useInventory();
  const { user } = useAuth();

  const [searchTerm, setSearchTerm] = useState('');
  const [filterCategory, setFilterCategory] = useState<string>('all');
  const [showItemDialog, setShowItemDialog] = useState(false);
  const [showExpenseDialog, setShowExpenseDialog] = useState(false);
  const [editingItem, setEditingItem] = useState<OperationalItem | null>(null);
  const [selectedItem, setSelectedItem] = useState<OperationalItem | null>(null);

  const [itemForm, setItemForm] = useState<Partial<OperationalItem>>({
    item_code: '',
    name: '',
    category: 'packaging',
    unit: 'pcs',
    current_stock: 0,
    min_stock: 0,
    unit_price: 0,
    supplier_name: '',
    notes: '',
  });

  const [expenseForm, setExpenseForm] = useState<Partial<OperationalExpense>>({
    transaction_type: 'purchase',
    quantity: 0,
    unit_price: 0,
    date: new Date().toISOString().split('T')[0],
    notes: '',
  });

  // Categories
  const categories = [
    { value: 'packaging', label: 'Packaging', icon: Package, color: 'blue' },
    { value: 'stationery', label: 'Stationery', icon: Clipboard, color: 'purple' },
    { value: 'cleaning', label: 'Cleaning', icon: Sparkles, color: 'green' },
    { value: 'utilities', label: 'Utilities', icon: PackageOpen, color: 'orange' },
    { value: 'other', label: 'Other', icon: PackageOpen, color: 'gray' },
  ];

  // Filter items
  const filteredItems = useMemo(() => {
    return operationalItems.filter(item => {
      const matchesSearch = item.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        item.item_code.toLowerCase().includes(searchTerm.toLowerCase());
      const matchesCategory = filterCategory === 'all' || item.category === filterCategory;
      return matchesSearch && matchesCategory;
    });
  }, [operationalItems, searchTerm, filterCategory]);

  // Calculate total expense this month
  const monthlyExpense = useMemo(() => {
    const now = new Date();
    const thisMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
    
    return operationalExpenses
      .filter(exp => exp.date.startsWith(thisMonth))
      .reduce((sum, exp) => sum + exp.total_amount, 0);
  }, [operationalExpenses]);

  // Low stock items
  const lowStockItems = useMemo(() => {
    return operationalItems.filter(item => item.current_stock <= item.min_stock);
  }, [operationalItems]);

  const getStockStatus = (item: OperationalItem) => {
    if (item.current_stock === 0) {
      return { label: 'Out of Stock', color: 'destructive', icon: AlertTriangle };
    } else if (item.current_stock <= item.min_stock) {
      return { label: 'Low Stock', color: 'warning', icon: TrendingDown };
    }
    return { label: 'In Stock', color: 'success', icon: Package };
  };

  const getCategoryInfo = (categoryValue: string) => {
    return categories.find(cat => cat.value === categoryValue) || categories[4];
  };

  const handleOpenItemDialog = (item?: OperationalItem) => {
    if (item) {
      setEditingItem(item);
      setItemForm(item);
    } else {
      setEditingItem(null);
      setItemForm({
        item_code: '',
        name: '',
        category: 'packaging',
        unit: 'pcs',
        current_stock: 0,
        min_stock: 0,
        unit_price: 0,
        supplier_name: '',
        notes: '',
      });
    }
    setShowItemDialog(true);
  };

  const handleSaveItem = () => {
    if (!itemForm.name || !itemForm.item_code) {
      toast.error('Please fill in all required fields');
      return;
    }

    if (editingItem) {
      updateOperationalItem(editingItem.id, itemForm);
      toast.success('Item updated successfully!');
    } else {
      addOperationalItem(itemForm as Omit<OperationalItem, 'id' | 'created_at' | 'updated_at'>);
      toast.success('Item added successfully!');
    }

    setShowItemDialog(false);
  };

  const handleDeleteItem = (id: string, name: string) => {
    if (window.confirm(`Delete "${name}"?`)) {
      deleteOperationalItem(id);
      toast.success('Item deleted successfully!');
    }
  };

  const handleOpenExpenseDialog = (item: OperationalItem, type: 'purchase' | 'usage') => {
    setSelectedItem(item);
    setExpenseForm({
      transaction_type: type,
      quantity: 0,
      unit_price: item.unit_price,
      date: new Date().toISOString().split('T')[0],
      notes: '',
    });
    setShowExpenseDialog(true);
  };

  const handleSaveExpense = () => {
    if (!selectedItem || !expenseForm.quantity || expenseForm.quantity <= 0) {
      toast.error('Please enter a valid quantity');
      return;
    }

    const totalAmount = (expenseForm.quantity || 0) * (expenseForm.unit_price || 0);

    addOperationalExpense({
      item_id: selectedItem.id,
      item_name: selectedItem.name,
      transaction_type: expenseForm.transaction_type!,
      quantity: expenseForm.quantity!,
      unit_price: expenseForm.unit_price!,
      total_amount: totalAmount,
      date: expenseForm.date!,
      notes: expenseForm.notes,
      created_by: user?.id || '',
    } as Omit<OperationalExpense, 'id' | 'created_at'>);

    toast.success(
      expenseForm.transaction_type === 'purchase'
        ? 'Purchase recorded successfully!'
        : 'Usage recorded successfully!'
    );

    setShowExpenseDialog(false);
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return '';
    return new Intl.DateTimeFormat('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    }).format(date);
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">Barang Operasional</h1>
          <p className="text-slate-600 mt-1">
            Kelola stok barang operasional toko (kantong kresek, struk, dll)
          </p>
        </div>
        <Button onClick={() => handleOpenItemDialog()}>
          <Plus className="w-4 h-4 mr-2" />
          Tambah Barang
        </Button>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card>
          <CardHeader className="pb-3">
            <CardTitle className="text-sm font-medium text-slate-600">Total Items</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex items-center justify-between">
              <div>
                <div className="text-3xl font-bold text-slate-900">{operationalItems.length}</div>
                <p className="text-xs text-slate-500 mt-1">Jenis barang operasional</p>
              </div>
              <Package className="w-12 h-12 text-blue-500 opacity-20" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-3">
            <CardTitle className="text-sm font-medium text-slate-600">Low Stock Alert</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex items-center justify-between">
              <div>
                <div className="text-3xl font-bold text-orange-600">{lowStockItems.length}</div>
                <p className="text-xs text-slate-500 mt-1">Items below minimum stock</p>
              </div>
              <AlertTriangle className="w-12 h-12 text-orange-500 opacity-20" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-3">
            <CardTitle className="text-sm font-medium text-slate-600">Pengeluaran Bulan Ini</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex items-center justify-between">
              <div>
                <div className="text-2xl font-bold text-slate-900">{formatCurrency(monthlyExpense)}</div>
                <p className="text-xs text-slate-500 mt-1">Total biaya operasional</p>
              </div>
              <DollarSign className="w-12 h-12 text-green-500 opacity-20" />
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Filters */}
      <Card>
        <CardContent className="pt-6">
          <div className="flex flex-col md:flex-row gap-4">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 w-4 h-4" />
              <Input
                placeholder="Search by name or code..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
              />
            </div>
            <div className="flex gap-2 flex-wrap">
              <Button
                variant={filterCategory === 'all' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setFilterCategory('all')}
              >
                All
              </Button>
              {categories.map((cat) => (
                <Button
                  key={cat.value}
                  variant={filterCategory === cat.value ? 'default' : 'outline'}
                  size="sm"
                  onClick={() => setFilterCategory(cat.value)}
                >
                  <cat.icon className="w-3 h-3 mr-1" />
                  {cat.label}
                </Button>
              ))}
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Items Table */}
      <Card>
        <CardHeader>
          <CardTitle>Daftar Barang Operasional</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Code</TableHead>
                  <TableHead>Name</TableHead>
                  <TableHead>Category</TableHead>
                  <TableHead>Stock</TableHead>
                  <TableHead>Unit Price</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Last Purchase</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {filteredItems.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={8} className="text-center py-8 text-slate-500">
                      No items found
                    </TableCell>
                  </TableRow>
                ) : (
                  filteredItems.map((item) => {
                    const status = getStockStatus(item);
                    const categoryInfo = getCategoryInfo(item.category);
                    const StatusIcon = status.icon;
                    const CategoryIcon = categoryInfo.icon;

                    return (
                      <TableRow key={item.id}>
                        <TableCell className="font-mono text-sm">{item.item_code}</TableCell>
                        <TableCell>
                          <div className="font-medium text-slate-900">{item.name}</div>
                          {item.notes && (
                            <div className="text-xs text-slate-500 mt-1">{item.notes}</div>
                          )}
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline" className="flex items-center gap-1 w-fit">
                            <CategoryIcon className="w-3 h-3" />
                            {categoryInfo.label}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <div className="font-semibold text-slate-900">
                            {item.current_stock} {item.unit}
                          </div>
                          <div className="text-xs text-slate-500">Min: {item.min_stock}</div>
                        </TableCell>
                        <TableCell>{formatCurrency(item.unit_price)}</TableCell>
                        <TableCell>
                          <Badge variant={status.color as any} className="flex items-center gap-1 w-fit">
                            <StatusIcon className="w-3 h-3" />
                            {status.label}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          {item.last_purchase_date ? (
                            <div>
                              <div className="text-sm">{formatDate(item.last_purchase_date)}</div>
                              {item.last_purchase_quantity && (
                                <div className="text-xs text-slate-500">
                                  Qty: {item.last_purchase_quantity}
                                </div>
                              )}
                            </div>
                          ) : (
                            <span className="text-slate-400">-</span>
                          )}
                        </TableCell>
                        <TableCell>
                          <div className="flex gap-1">
                            <Button
                              variant="outline"
                              size="sm"
                              onClick={() => handleOpenExpenseDialog(item, 'purchase')}
                              title="Purchase"
                            >
                              <ShoppingCart className="w-3 h-3" />
                            </Button>
                            <Button
                              variant="outline"
                              size="sm"
                              onClick={() => handleOpenExpenseDialog(item, 'usage')}
                              title="Record Usage"
                            >
                              <TrendingDown className="w-3 h-3" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => handleOpenItemDialog(item)}
                            >
                              <Edit className="w-3 h-3" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => handleDeleteItem(item.id, item.name)}
                            >
                              <Trash2 className="w-3 h-3 text-red-600" />
                            </Button>
                          </div>
                        </TableCell>
                      </TableRow>
                    );
                  })
                )}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      {/* Add/Edit Item Dialog */}
      <Dialog open={showItemDialog} onOpenChange={setShowItemDialog}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>
              {editingItem ? 'Edit Barang Operasional' : 'Tambah Barang Operasional'}
            </DialogTitle>
            <DialogDescription>
              {editingItem ? 'Update informasi barang' : 'Tambahkan barang operasional baru'}
            </DialogDescription>
          </DialogHeader>

          <div className="space-y-4 mt-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label>Item Code *</Label>
                <Input
                  value={itemForm.item_code}
                  onChange={(e) => setItemForm({ ...itemForm, item_code: e.target.value })}
                  placeholder="OPS-001"
                />
              </div>
              <div>
                <Label>Category *</Label>
                <select
                  value={itemForm.category}
                  onChange={(e) => setItemForm({ ...itemForm, category: e.target.value as any })}
                  className="w-full px-3 py-2 border rounded-lg"
                >
                  {categories.map((cat) => (
                    <option key={cat.value} value={cat.value}>
                      {cat.label}
                    </option>
                  ))}
                </select>
              </div>
            </div>

            <div>
              <Label>Item Name *</Label>
              <Input
                value={itemForm.name}
                onChange={(e) => setItemForm({ ...itemForm, name: e.target.value })}
                placeholder="e.g., Kantong Kresek Kecil"
              />
            </div>

            <div className="grid grid-cols-3 gap-4">
              <div>
                <Label>Unit *</Label>
                <Input
                  value={itemForm.unit}
                  onChange={(e) => setItemForm({ ...itemForm, unit: e.target.value })}
                  placeholder="pcs, pack, roll"
                />
              </div>
              <div>
                <Label>Current Stock</Label>
                <Input
                  type="number"
                  value={itemForm.current_stock}
                  onChange={(e) => setItemForm({ ...itemForm, current_stock: Number(e.target.value) })}
                  placeholder="0"
                />
              </div>
              <div>
                <Label>Min Stock *</Label>
                <Input
                  type="number"
                  value={itemForm.min_stock}
                  onChange={(e) => setItemForm({ ...itemForm, min_stock: Number(e.target.value) })}
                  placeholder="0"
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label>Unit Price *</Label>
                <Input
                  type="number"
                  value={itemForm.unit_price}
                  onChange={(e) => setItemForm({ ...itemForm, unit_price: Number(e.target.value) })}
                  placeholder="15000"
                />
              </div>
              <div>
                <Label>Supplier Name</Label>
                <Input
                  value={itemForm.supplier_name}
                  onChange={(e) => setItemForm({ ...itemForm, supplier_name: e.target.value })}
                  placeholder="Supplier name"
                />
              </div>
            </div>

            <div>
              <Label>Notes</Label>
              <Input
                value={itemForm.notes}
                onChange={(e) => setItemForm({ ...itemForm, notes: e.target.value })}
                placeholder="Additional information"
              />
            </div>
          </div>

          <DialogFooter className="mt-6">
            <Button variant="outline" onClick={() => setShowItemDialog(false)}>
              Cancel
            </Button>
            <Button onClick={handleSaveItem}>
              {editingItem ? 'Update' : 'Add Item'}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Expense Dialog */}
      <Dialog open={showExpenseDialog} onOpenChange={setShowExpenseDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>
              {expenseForm.transaction_type === 'purchase' ? 'Record Purchase' : 'Record Usage'}
            </DialogTitle>
            <DialogDescription>
              {selectedItem?.name}
            </DialogDescription>
          </DialogHeader>

          <div className="space-y-4 mt-4">
            <div className="bg-slate-50 p-4 rounded-lg">
              <div className="flex items-center justify-between mb-2">
                <span className="text-sm text-slate-600">Current Stock:</span>
                <span className="font-semibold text-slate-900">
                  {selectedItem?.current_stock} {selectedItem?.unit}
                </span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-600">Unit Price:</span>
                <span className="font-semibold text-slate-900">
                  {formatCurrency(selectedItem?.unit_price || 0)}
                </span>
              </div>
            </div>

            <div>
              <Label>Date *</Label>
              <Input
                type="date"
                value={expenseForm.date}
                onChange={(e) => setExpenseForm({ ...expenseForm, date: e.target.value })}
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label>Quantity *</Label>
                <Input
                  type="number"
                  value={expenseForm.quantity}
                  onChange={(e) => setExpenseForm({ ...expenseForm, quantity: Number(e.target.value) })}
                  placeholder="0"
                />
              </div>
              <div>
                <Label>Unit Price</Label>
                <Input
                  type="number"
                  value={expenseForm.unit_price}
                  onChange={(e) => setExpenseForm({ ...expenseForm, unit_price: Number(e.target.value) })}
                  placeholder="0"
                />
              </div>
            </div>

            <div className="bg-blue-50 p-4 rounded-lg border border-blue-200">
              <div className="flex items-center justify-between">
                <span className="font-semibold text-blue-900">Total Amount:</span>
                <span className="text-2xl font-bold text-blue-900">
                  {formatCurrency((expenseForm.quantity || 0) * (expenseForm.unit_price || 0))}
                </span>
              </div>
            </div>

            <div>
              <Label>Notes</Label>
              <Input
                value={expenseForm.notes}
                onChange={(e) => setExpenseForm({ ...expenseForm, notes: e.target.value })}
                placeholder="Additional notes"
              />
            </div>
          </div>

          <DialogFooter className="mt-6">
            <Button variant="outline" onClick={() => setShowExpenseDialog(false)}>
              Cancel
            </Button>
            <Button onClick={handleSaveExpense}>
              {expenseForm.transaction_type === 'purchase' ? 'Record Purchase' : 'Record Usage'}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
};
