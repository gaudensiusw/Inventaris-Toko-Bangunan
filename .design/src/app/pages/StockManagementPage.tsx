import React, { useState } from 'react';
import { useInventory } from '../contexts/InventoryContext';
import { useAuth } from '../contexts/AuthContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Badge } from '../components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '../components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../components/ui/table';
import { Textarea } from '../components/ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../components/ui/tabs';
import { toast } from 'sonner';
import { TrendingUp, TrendingDown, RefreshCw, Plus, Minus, FileText, ArrowUpCircle, ArrowDownCircle } from 'lucide-react';

export const StockManagementPage: React.FC = () => {
  const { products, stockTransactions, addStockTransaction, adjustStock } = useInventory();
  const { user, hasPermission } = useAuth();
  
  const [showDialog, setShowDialog] = useState(false);
  const [dialogType, setDialogType] = useState<'in' | 'out' | 'adjustment'>('in');
  
  const [formData, setFormData] = useState({
    product_id: '',
    quantity: 0,
    unit_price: 0,
    reference_number: '',
    notes: '',
    reason: '',
  });

  const handleOpenDialog = (type: 'in' | 'out' | 'adjustment') => {
    setDialogType(type);
    setFormData({
      product_id: '',
      quantity: 0,
      unit_price: 0,
      reference_number: '',
      notes: '',
      reason: '',
    });
    setShowDialog(true);
  };

  const handleProductSelect = (productId: string) => {
    const product = products.find(p => p.id === productId);
    if (product) {
      setFormData(prev => ({
        ...prev,
        product_id: productId,
        unit_price: dialogType === 'in' ? product.hpp : product.selling_price,
      }));
    }
  };

  const handleSubmit = () => {
    if (!formData.product_id || formData.quantity === 0) {
      toast.error('Please fill in all required fields');
      return;
    }

    const product = products.find(p => p.id === formData.product_id);
    if (!product) return;

    if (dialogType === 'adjustment') {
      if (!formData.reason) {
        toast.error('Please provide a reason for stock adjustment');
        return;
      }
      adjustStock(
        formData.product_id,
        formData.quantity,
        formData.reason,
        `${user?.role} - ${user?.name}`
      );
      toast.success('Stock adjusted successfully');
    } else {
      const quantity = dialogType === 'out' ? -Math.abs(formData.quantity) : Math.abs(formData.quantity);
      
      if (dialogType === 'out' && Math.abs(quantity) > product.current_stock) {
        toast.error('Insufficient stock available');
        return;
      }

      addStockTransaction({
        product_id: formData.product_id,
        transaction_type: dialogType,
        quantity,
        unit_price: formData.unit_price,
        total_amount: quantity * formData.unit_price,
        reference_number: formData.reference_number || `${dialogType.toUpperCase()}-${Date.now()}`,
        notes: formData.notes,
        created_by: user?.id || 'unknown',
      });
      
      toast.success(`Stock ${dialogType} recorded successfully`);
    }

    setShowDialog(false);
  };

  const recentTransactions = [...stockTransactions]
    .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
    .slice(0, 20);

  const getTransactionIcon = (type: string) => {
    switch (type) {
      case 'in':
        return <ArrowDownCircle className="w-4 h-4 text-green-600" />;
      case 'out':
        return <ArrowUpCircle className="w-4 h-4 text-red-600" />;
      case 'adjustment':
        return <RefreshCw className="w-4 h-4 text-blue-600" />;
      case 'sale':
        return <ArrowUpCircle className="w-4 h-4 text-purple-600" />;
      default:
        return <FileText className="w-4 h-4" />;
    }
  };

  const getTransactionBadge = (type: string) => {
    const variants: any = {
      in: 'default',
      out: 'destructive',
      adjustment: 'secondary',
      sale: 'default',
    };
    return variants[type] || 'default';
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900">Stock Management</h2>
          <p className="text-slate-600">Track and manage inventory movements</p>
        </div>
      </div>

      {/* Action Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        {hasPermission('stock.in') && (
          <Card className="cursor-pointer hover:shadow-lg transition-shadow" onClick={() => handleOpenDialog('in')}>
            <CardContent className="pt-6">
              <div className="flex items-center gap-4">
                <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                  <TrendingUp className="w-6 h-6 text-green-600" />
                </div>
                <div>
                  <h3 className="font-bold">Stock In</h3>
                  <p className="text-sm text-slate-600">Receive new stock</p>
                </div>
              </div>
            </CardContent>
          </Card>
        )}

        {hasPermission('stock.out') && (
          <Card className="cursor-pointer hover:shadow-lg transition-shadow" onClick={() => handleOpenDialog('out')}>
            <CardContent className="pt-6">
              <div className="flex items-center gap-4">
                <div className="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                  <TrendingDown className="w-6 h-6 text-red-600" />
                </div>
                <div>
                  <h3 className="font-bold">Stock Out</h3>
                  <p className="text-sm text-slate-600">Record stock removal</p>
                </div>
              </div>
            </CardContent>
          </Card>
        )}

        {hasPermission('stock.adjust') && (
          <Card className="cursor-pointer hover:shadow-lg transition-shadow" onClick={() => handleOpenDialog('adjustment')}>
            <CardContent className="pt-6">
              <div className="flex items-center gap-4">
                <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                  <RefreshCw className="w-6 h-6 text-blue-600" />
                </div>
                <div>
                  <h3 className="font-bold">Stock Adjustment</h3>
                  <p className="text-sm text-slate-600">Adjust stock levels</p>
                </div>
              </div>
            </CardContent>
          </Card>
        )}
      </div>

      {/* Stock Levels */}
      <Card>
        <CardHeader>
          <CardTitle>Current Stock Levels</CardTitle>
          <CardDescription>Overview of all product inventory</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Product</TableHead>
                  <TableHead>SKU</TableHead>
                  <TableHead>Current Stock</TableHead>
                  <TableHead>Min / Max</TableHead>
                  <TableHead>Stock Value</TableHead>
                  <TableHead>Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {products.map(product => {
                  const stockValue = product.current_stock * product.hpp;
                  const stockPercentage = (product.current_stock / product.max_stock) * 100;
                  
                  let status = { label: 'Normal', variant: 'default' as any };
                  if (product.current_stock < product.min_stock) {
                    status = { label: 'Low Stock', variant: 'destructive' as any };
                  } else if (stockPercentage > 90) {
                    status = { label: 'High Stock', variant: 'secondary' as any };
                  }

                  return (
                    <TableRow key={product.id}>
                      <TableCell>
                        <div>
                          <p className="font-medium">{product.name}</p>
                          <p className="text-xs text-slate-500">{product.description}</p>
                        </div>
                      </TableCell>
                      <TableCell className="font-mono text-sm">{product.sku}</TableCell>
                      <TableCell>
                        <p className="font-bold">{product.current_stock}</p>
                        <p className="text-xs text-slate-500">{product.base_unit}</p>
                      </TableCell>
                      <TableCell className="text-sm">
                        {product.min_stock} / {product.max_stock}
                      </TableCell>
                      <TableCell>
                        Rp {stockValue.toLocaleString()}
                      </TableCell>
                      <TableCell>
                        <Badge variant={status.variant}>
                          {status.label}
                        </Badge>
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      {/* Transaction History */}
      <Card>
        <CardHeader>
          <CardTitle>Recent Transactions</CardTitle>
          <CardDescription>Latest stock movements</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Date</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Product</TableHead>
                  <TableHead>Quantity</TableHead>
                  <TableHead>Unit Price</TableHead>
                  <TableHead>Total</TableHead>
                  <TableHead>Reference</TableHead>
                  <TableHead>Notes</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {recentTransactions.map(transaction => {
                  const product = products.find(p => p.id === transaction.product_id);
                  
                  return (
                    <TableRow key={transaction.id}>
                      <TableCell className="text-sm">
                        {new Date(transaction.created_at).toLocaleDateString('id-ID', {
                          day: '2-digit',
                          month: 'short',
                          year: 'numeric',
                          hour: '2-digit',
                          minute: '2-digit',
                        })}
                      </TableCell>
                      <TableCell>
                        <Badge variant={getTransactionBadge(transaction.transaction_type)} className="flex items-center gap-1 w-fit">
                          {getTransactionIcon(transaction.transaction_type)}
                          {transaction.transaction_type}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <div>
                          <p className="font-medium text-sm">{product?.name}</p>
                          <p className="text-xs text-slate-500">{product?.sku}</p>
                        </div>
                      </TableCell>
                      <TableCell>
                        <span className={transaction.quantity > 0 ? 'text-green-600' : 'text-red-600'}>
                          {transaction.quantity > 0 ? '+' : ''}{transaction.quantity}
                        </span>
                      </TableCell>
                      <TableCell>
                        Rp {transaction.unit_price.toLocaleString()}
                      </TableCell>
                      <TableCell>
                        Rp {Math.abs(transaction.total_amount).toLocaleString()}
                      </TableCell>
                      <TableCell className="font-mono text-xs">
                        {transaction.reference_number}
                      </TableCell>
                      <TableCell className="text-sm text-slate-600">
                        {transaction.notes}
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      {/* Stock In/Out/Adjustment Dialog */}
      <Dialog open={showDialog} onOpenChange={setShowDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>
              {dialogType === 'in' && 'Stock In'}
              {dialogType === 'out' && 'Stock Out'}
              {dialogType === 'adjustment' && 'Stock Adjustment'}
            </DialogTitle>
            <DialogDescription>
              {dialogType === 'in' && 'Record incoming stock from supplier'}
              {dialogType === 'out' && 'Record stock removal or dispatch'}
              {dialogType === 'adjustment' && 'Adjust stock levels (requires reason)'}
            </DialogDescription>
          </DialogHeader>

          <div className="space-y-4">
            <div>
              <Label>Product *</Label>
              <Select value={formData.product_id} onValueChange={handleProductSelect}>
                <SelectTrigger>
                  <SelectValue placeholder="Select product" />
                </SelectTrigger>
                <SelectContent>
                  {products.map(product => (
                    <SelectItem key={product.id} value={product.id}>
                      {product.name} ({product.sku}) - Stock: {product.current_stock}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label>Quantity *</Label>
                <Input
                  type="number"
                  value={formData.quantity}
                  onChange={(e) => setFormData({ ...formData, quantity: Number(e.target.value) })}
                  placeholder={dialogType === 'adjustment' ? 'Positive or negative' : 'Enter quantity'}
                />
              </div>
              <div>
                <Label>Unit Price</Label>
                <Input
                  type="number"
                  value={formData.unit_price}
                  onChange={(e) => setFormData({ ...formData, unit_price: Number(e.target.value) })}
                />
              </div>
            </div>

            {dialogType !== 'adjustment' && (
              <div>
                <Label>Reference Number</Label>
                <Input
                  value={formData.reference_number}
                  onChange={(e) => setFormData({ ...formData, reference_number: e.target.value })}
                  placeholder="e.g., PO-2026-001"
                />
              </div>
            )}

            <div>
              <Label>{dialogType === 'adjustment' ? 'Reason *' : 'Notes'}</Label>
              <Textarea
                value={dialogType === 'adjustment' ? formData.reason : formData.notes}
                onChange={(e) => setFormData({ ...formData, [dialogType === 'adjustment' ? 'reason' : 'notes']: e.target.value })}
                placeholder={dialogType === 'adjustment' ? 'Explain why adjustment is needed' : 'Optional notes'}
                rows={3}
              />
            </div>

            <div className="border-t pt-4">
              <div className="flex justify-between text-sm mb-2">
                <span>Quantity:</span>
                <span className="font-medium">{formData.quantity}</span>
              </div>
              <div className="flex justify-between text-sm mb-2">
                <span>Unit Price:</span>
                <span className="font-medium">Rp {formData.unit_price.toLocaleString()}</span>
              </div>
              <div className="flex justify-between font-bold">
                <span>Total Value:</span>
                <span>Rp {(formData.quantity * formData.unit_price).toLocaleString()}</span>
              </div>
            </div>

            <div className="flex justify-end gap-3">
              <Button variant="outline" onClick={() => setShowDialog(false)}>
                Cancel
              </Button>
              <Button onClick={handleSubmit}>
                Submit
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
};
