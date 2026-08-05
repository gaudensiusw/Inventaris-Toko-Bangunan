import React, { useState } from 'react';
import { useInventory } from '../contexts/InventoryContext';
import { useAuth } from '../contexts/AuthContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Badge } from '../components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '../components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../components/ui/table';
import { Textarea } from '../components/ui/textarea';
import { Slider } from '../components/ui/slider';
import { toast } from 'sonner';
import { DollarSign, Calculator, TrendingUp, Settings, Info } from 'lucide-react';

export const PricingEnginePage: React.FC = () => {
  const { products, updatePrice } = useInventory();
  const { user } = useAuth();
  const [selectedProduct, setSelectedProduct] = useState<string | null>(null);
  const [showPricingDialog, setShowPricingDialog] = useState(false);
  
  const [pricingData, setPricingData] = useState({
    hpp: 0,
    margin: 0,
    tax: 11,
    sellingPrice: 0,
    reason: '',
  });

  const handleOpenPricingDialog = (productId: string) => {
    const product = products.find(p => p.id === productId);
    if (product) {
      setSelectedProduct(productId);
      setPricingData({
        hpp: product.hpp,
        margin: product.margin_percentage,
        tax: product.tax_percentage,
        sellingPrice: product.selling_price,
        reason: '',
      });
      setShowPricingDialog(true);
    }
  };

  const calculatePrice = (hpp: number, margin: number, tax: number) => {
    const basePrice = hpp * (1 + margin / 100);
    const finalPrice = basePrice * (1 + tax / 100);
    return Math.round(finalPrice);
  };

  const handleHppChange = (value: number) => {
    const newPrice = calculatePrice(value, pricingData.margin, pricingData.tax);
    setPricingData({ ...pricingData, hpp: value, sellingPrice: newPrice });
  };

  const handleMarginChange = (value: number) => {
    const newPrice = calculatePrice(pricingData.hpp, value, pricingData.tax);
    setPricingData({ ...pricingData, margin: value, sellingPrice: newPrice });
  };

  const handleTaxChange = (value: number) => {
    const newPrice = calculatePrice(pricingData.hpp, pricingData.margin, value);
    setPricingData({ ...pricingData, tax: value, sellingPrice: newPrice });
  };

  const handleUpdatePrice = () => {
    if (!selectedProduct) return;
    
    if (!pricingData.reason) {
      toast.error('Please provide a reason for the price change');
      return;
    }

    updatePrice(
      selectedProduct,
      pricingData.sellingPrice,
      pricingData.reason,
      `${user?.role} - ${user?.name}`
    );

    toast.success('Price updated successfully');
    setShowPricingDialog(false);
  };

  const getMarginColor = (margin: number) => {
    if (margin >= 20) return 'text-green-600';
    if (margin >= 10) return 'text-yellow-600';
    return 'text-red-600';
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900">Dynamic Pricing Engine</h2>
          <p className="text-slate-600">Formula-based pricing management</p>
        </div>
      </div>

      {/* Pricing Formula Info */}
      <Card className="border-blue-200 bg-blue-50">
        <CardContent className="pt-6">
          <div className="flex items-start gap-3">
            <Info className="w-5 h-5 text-blue-600 mt-0.5" />
            <div>
              <h3 className="font-medium text-blue-900 mb-2">Pricing Formula</h3>
              <div className="space-y-2 text-sm text-blue-800">
                <p><strong>Selling Price = HPP × (1 + Margin%) × (1 + Tax%)</strong></p>
                <p>• HPP (Harga Pokok Penjualan) = Cost of Goods Sold (COGS)</p>
                <p>• Margin% = Your profit margin percentage</p>
                <p>• Tax% = Applicable tax rate (default: 11% PPN)</p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Stats */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Avg Margin</CardTitle>
            <TrendingUp className="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {(products.reduce((sum, p) => sum + p.margin_percentage, 0) / products.length).toFixed(1)}%
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">High Margin</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-green-600">
              {products.filter(p => p.margin_percentage >= 20).length}
            </div>
            <p className="text-xs text-slate-500">≥20% margin</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Medium Margin</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-yellow-600">
              {products.filter(p => p.margin_percentage >= 10 && p.margin_percentage < 20).length}
            </div>
            <p className="text-xs text-slate-500">10-20% margin</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Low Margin</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-red-600">
              {products.filter(p => p.margin_percentage < 10).length}
            </div>
            <p className="text-xs text-slate-500">&lt;10% margin</p>
          </CardContent>
        </Card>
      </div>

      {/* Products Pricing Table */}
      <Card>
        <CardHeader>
          <CardTitle>Product Pricing Overview</CardTitle>
          <CardDescription>View and manage pricing for all products</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Product</TableHead>
                  <TableHead>HPP (COGS)</TableHead>
                  <TableHead>Margin %</TableHead>
                  <TableHead>Tax %</TableHead>
                  <TableHead>Selling Price</TableHead>
                  <TableHead>Gross Profit</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {products.map(product => {
                  const grossProfit = product.selling_price - product.hpp;
                  
                  return (
                    <TableRow key={product.id}>
                      <TableCell>
                        <div>
                          <p className="font-medium">{product.name}</p>
                          <p className="text-xs text-slate-500">{product.sku}</p>
                        </div>
                      </TableCell>
                      <TableCell>
                        <p className="font-mono">Rp {product.hpp.toLocaleString()}</p>
                      </TableCell>
                      <TableCell>
                        <Badge variant={product.margin_percentage >= 20 ? 'default' : 'secondary'} className={getMarginColor(product.margin_percentage)}>
                          {product.margin_percentage.toFixed(1)}%
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <span className="text-sm">{product.tax_percentage}%</span>
                      </TableCell>
                      <TableCell>
                        <p className="font-bold">Rp {product.selling_price.toLocaleString()}</p>
                      </TableCell>
                      <TableCell>
                        <p className="font-medium text-green-600">
                          Rp {grossProfit.toLocaleString()}
                        </p>
                      </TableCell>
                      <TableCell>
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleOpenPricingDialog(product.id)}
                        >
                          <Calculator className="w-4 h-4 mr-2" />
                          Adjust
                        </Button>
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      {/* Pricing Dialog */}
      <Dialog open={showPricingDialog} onOpenChange={setShowPricingDialog}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Dynamic Pricing Calculator</DialogTitle>
            <DialogDescription>
              Adjust pricing parameters and see real-time calculations
            </DialogDescription>
          </DialogHeader>

          <div className="space-y-6">
            {/* HPP Input */}
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <Label>HPP (Cost of Goods Sold)</Label>
                <span className="text-sm font-mono">Rp {pricingData.hpp.toLocaleString()}</span>
              </div>
              <Input
                type="number"
                value={pricingData.hpp}
                onChange={(e) => handleHppChange(Number(e.target.value))}
              />
            </div>

            {/* Margin Slider */}
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <Label>Profit Margin</Label>
                <span className="text-sm font-bold text-blue-600">{pricingData.margin.toFixed(1)}%</span>
              </div>
              <Slider
                value={[pricingData.margin]}
                onValueChange={([value]) => handleMarginChange(value)}
                min={0}
                max={100}
                step={0.5}
              />
              <div className="flex justify-between text-xs text-slate-500">
                <span>0%</span>
                <span>50%</span>
                <span>100%</span>
              </div>
            </div>

            {/* Tax Slider */}
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <Label>Tax (PPN)</Label>
                <span className="text-sm font-bold text-orange-600">{pricingData.tax.toFixed(1)}%</span>
              </div>
              <Slider
                value={[pricingData.tax]}
                onValueChange={([value]) => handleTaxChange(value)}
                min={0}
                max={15}
                step={0.5}
              />
              <div className="flex justify-between text-xs text-slate-500">
                <span>0%</span>
                <span>11% (Standard)</span>
                <span>15%</span>
              </div>
            </div>

            {/* Calculation Breakdown */}
            <Card className="bg-slate-50">
              <CardContent className="pt-6 space-y-2">
                <div className="flex justify-between text-sm">
                  <span>Base HPP:</span>
                  <span className="font-mono">Rp {pricingData.hpp.toLocaleString()}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span>+ Margin ({pricingData.margin.toFixed(1)}%):</span>
                  <span className="font-mono text-green-600">
                    Rp {(pricingData.hpp * (pricingData.margin / 100)).toLocaleString()}
                  </span>
                </div>
                <div className="flex justify-between text-sm">
                  <span>Subtotal:</span>
                  <span className="font-mono">
                    Rp {(pricingData.hpp * (1 + pricingData.margin / 100)).toLocaleString()}
                  </span>
                </div>
                <div className="flex justify-between text-sm">
                  <span>+ Tax ({pricingData.tax.toFixed(1)}%):</span>
                  <span className="font-mono text-orange-600">
                    Rp {(pricingData.hpp * (1 + pricingData.margin / 100) * (pricingData.tax / 100)).toLocaleString()}
                  </span>
                </div>
                <div className="border-t pt-2 flex justify-between text-lg font-bold">
                  <span>Final Selling Price:</span>
                  <span className="font-mono text-blue-600">
                    Rp {pricingData.sellingPrice.toLocaleString()}
                  </span>
                </div>
              </CardContent>
            </Card>

            {/* Reason */}
            <div>
              <Label>Reason for Price Change *</Label>
              <Textarea
                value={pricingData.reason}
                onChange={(e) => setPricingData({ ...pricingData, reason: e.target.value })}
                placeholder="e.g., Supplier price increase, market adjustment, etc."
                rows={3}
              />
            </div>

            <div className="flex justify-end gap-3">
              <Button variant="outline" onClick={() => setShowPricingDialog(false)}>
                Cancel
              </Button>
              <Button onClick={handleUpdatePrice}>
                Update Price
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
};