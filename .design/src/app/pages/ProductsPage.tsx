import React, { useState } from 'react';
import { useInventory } from '../contexts/InventoryContext';
import { useAuth } from '../contexts/AuthContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Textarea } from '../components/ui/textarea';
import { Badge } from '../components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '../components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../components/ui/table';
import { toast } from 'sonner';
import { Package, Plus, Edit, Trash2, Search, TrendingUp, TrendingDown, AlertTriangle, Upload, X, Eye, Barcode, CheckCircle } from 'lucide-react';
import { Product } from '../data/mockData';
import { StockDisplay } from '../components/StockDisplay';
import { generateEAN13Barcode, generateSimpleBarcode, generateBarcodeFromSKU, validateEAN13Barcode, formatBarcodeDisplay } from '../utils/barcodeGenerator';

export const ProductsPage: React.FC = () => {
  const { products, categories, suppliers, addProduct, updateProduct, deleteProduct } = useInventory();
  const { user, hasPermission } = useAuth();
  
  const [searchTerm, setSearchTerm] = useState('');
  const [filterCategory, setFilterCategory] = useState('all');
  const [showDialog, setShowDialog] = useState(false);
  const [showPreviewDialog, setShowPreviewDialog] = useState(false);
  const [previewProduct, setPreviewProduct] = useState<Product | null>(null);
  const [editingProduct, setEditingProduct] = useState<Product | null>(null);
  
  // Form state
  const [formData, setFormData] = useState<Partial<Product>>({
    sku: '',
    name: '',
    brand: '',
    category_id: '',
    sub_category: '',
    supplier_id: '',
    description: '',
    image_url: '',
    base_unit: 'piece',
    units: [],
    current_stock: 0,
    min_stock: 0,
    max_stock: 0,
    hpp: 0,
    selling_price: 0,
    margin_percentage: 0,
    tax_percentage: 11,
    last_purchase_date: new Date().toISOString(),
    last_purchase_price: 0,
  });

  const filteredProducts = products.filter(product => {
    const matchesSearch = product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      product.sku.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (product.brand && product.brand.toLowerCase().includes(searchTerm.toLowerCase()));
    const matchesCategory = filterCategory === 'all' || product.category_id === filterCategory;
    return matchesSearch && matchesCategory;
  });

  const handleOpenDialog = (product?: Product) => {
    if (product) {
      setEditingProduct(product);
      setFormData({ ...product });
    } else {
      setEditingProduct(null);
      setFormData({
        sku: '',
        name: '',
        brand: '',
        category_id: '',
        sub_category: '',
        supplier_id: '',
        description: '',
        image_url: '',
        base_unit: 'piece',
        units: [],
        current_stock: 0,
        min_stock: 0,
        max_stock: 0,
        hpp: 0,
        selling_price: 0,
        margin_percentage: 0,
        tax_percentage: 11,
        last_purchase_date: new Date().toISOString(),
        last_purchase_price: 0,
      });
    }
    setShowDialog(true);
  };

  const calculateMargin = (hpp: number, sellingPrice: number) => {
    if (hpp === 0) return 0;
    return ((sellingPrice - hpp) / hpp) * 100;
  };

  const handleHppChange = (value: number) => {
    setFormData(prev => {
      const newMargin = calculateMargin(value, prev.selling_price);
      return { ...prev, hpp: value, margin_percentage: newMargin };
    });
  };

  const handleSellingPriceChange = (value: number) => {
    setFormData(prev => {
      const newMargin = calculateMargin(prev.hpp, value);
      return { ...prev, selling_price: value, margin_percentage: newMargin };
    });
  };

  const handleMarginChange = (value: number) => {
    setFormData(prev => {
      const newSellingPrice = prev.hpp * (1 + value / 100);
      return { ...prev, margin_percentage: value, selling_price: Math.round(newSellingPrice) };
    });
  };

  const handleSubmit = () => {
    if (!formData.name || !formData.sku || !formData.category_id || !formData.supplier_id) {
      toast.error('Please fill in all required fields');
      return;
    }

    if (editingProduct) {
      updateProduct(
        editingProduct.id,
        formData,
        'Product information updated',
        `${user?.role} - ${user?.name}`
      );
      toast.success('Product updated successfully');
    } else {
      addProduct(formData);
      toast.success('Product added successfully');
    }

    setShowDialog(false);
  };

  const handleDelete = (productId: string, productName: string) => {
    if (window.confirm(`Are you sure you want to delete "${productName}"?`)) {
      deleteProduct(productId);
      toast.success('Product deleted');
    }
  };

  const getStockStatus = (product: Product) => {
    if (product.current_stock < product.min_stock) {
      return { label: 'Low Stock', color: 'destructive', icon: TrendingDown };
    } else if (product.current_stock > product.max_stock * 0.9) {
      return { label: 'High Stock', color: 'secondary', icon: TrendingUp };
    }
    return { label: 'Normal', color: 'default', icon: Package };
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900">Product Management</h2>
          <p className="text-slate-600">Manage your inventory products</p>
        </div>
        {hasPermission('products.create') && (
          <Button onClick={() => handleOpenDialog()}>
            <Plus className="w-4 h-4 mr-2" />
            Add Product
          </Button>
        )}
      </div>

      {/* Filters */}
      <Card>
        <CardContent className="pt-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="relative">
              <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
              <Input
                placeholder="Search products..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
              />
            </div>
            <Select value={filterCategory} onValueChange={setFilterCategory}>
              <SelectTrigger>
                <SelectValue placeholder="Filter by category" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Categories</SelectItem>
                {categories.map(cat => (
                  <SelectItem key={cat.id} value={cat.id}>{cat.name}</SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        </CardContent>
      </Card>

      {/* Products Table */}
      <Card>
        <CardHeader>
          <CardTitle>Products ({filteredProducts.length})</CardTitle>
          <CardDescription>View and manage all products</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>SKU</TableHead>
                  <TableHead>Brand</TableHead>
                  <TableHead>Product Name</TableHead>
                  <TableHead>Category</TableHead>
                  <TableHead>Stock</TableHead>
                  <TableHead>HPP</TableHead>
                  <TableHead>Selling Price</TableHead>
                  <TableHead>Margin</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {filteredProducts.map(product => {
                  const category = categories.find(c => c.id === product.category_id);
                  const status = getStockStatus(product);
                  const StatusIcon = status.icon;

                  return (
                    <TableRow key={product.id}>
                      <TableCell className="font-mono text-sm">{product.sku}</TableCell>
                      <TableCell>
                        <Badge variant="secondary" className="font-medium">
                          {product.brand || '-'}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <div>
                          <p className="font-medium">{product.name}</p>
                          {product.sub_category && (
                            <p className="text-xs text-slate-500">
                              <span className="text-slate-400">•</span> {product.sub_category}
                            </p>
                          )}
                        </div>
                      </TableCell>
                      <TableCell>
                        <Badge variant="outline">{category?.name}</Badge>
                      </TableCell>
                      <TableCell>
                        <StockDisplay product={product} compact />
                      </TableCell>
                      <TableCell>
                        Rp {product.hpp.toLocaleString()}
                      </TableCell>
                      <TableCell>
                        Rp {product.selling_price.toLocaleString()}
                      </TableCell>
                      <TableCell>
                        <Badge variant="secondary">
                          {product.margin_percentage.toFixed(1)}%
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <Badge variant={status.color as any} className="flex items-center gap-1 w-fit">
                          <StatusIcon className="w-3 h-3" />
                          {status.label}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <div className="flex gap-2">
                          <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => {
                              setPreviewProduct(product);
                              setShowPreviewDialog(true);
                            }}
                            title="View Details"
                          >
                            <Eye className="w-4 h-4" />
                          </Button>
                          {hasPermission('products.edit') && (
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleOpenDialog(product)}
                            >
                              <Edit className="w-4 h-4" />
                            </Button>
                          )}
                          {hasPermission('products.delete') && (
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleDelete(product.id, product.name)}
                            >
                              <Trash2 className="w-4 h-4 text-red-600" />
                            </Button>
                          )}
                        </div>
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      {/* Add/Edit Product Dialog */}
      <Dialog open={showDialog} onOpenChange={setShowDialog}>
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>
              {editingProduct ? 'Edit Product' : 'Add New Product'}
            </DialogTitle>
            <DialogDescription>
              {editingProduct ? 'Update product information' : 'Create a new product in inventory'}
            </DialogDescription>
          </DialogHeader>

          <div className="space-y-4">
            {/* Product Image */}
            <div className="border-2 border-dashed border-slate-300 rounded-lg p-4 bg-slate-50">
              <Label className="mb-2 block">Product Image</Label>
              <div className="flex items-start gap-4">
                {formData.image_url ? (
                  <div className="relative">
                    <div className="w-32 h-32 rounded-lg overflow-hidden border-2 border-slate-200">
                      <img
                        src={formData.image_url}
                        alt="Product preview"
                        className="w-full h-full object-cover"
                        onError={(e) => {
                          e.currentTarget.src = 'https://via.placeholder.com/150?text=No+Image';
                        }}
                      />
                    </div>
                    <Button
                      variant="destructive"
                      size="icon"
                      className="absolute -top-2 -right-2 h-6 w-6 rounded-full"
                      onClick={() => setFormData({ ...formData, image_url: '' })}
                    >
                      <X className="h-3 w-3" />
                    </Button>
                  </div>
                ) : (
                  <div className="w-32 h-32 rounded-lg border-2 border-dashed border-slate-300 flex items-center justify-center bg-white">
                    <Upload className="h-8 w-8 text-slate-400" />
                  </div>
                )}
                <div className="flex-1">
                  <Input
                    value={formData.image_url || ''}
                    onChange={(e) => setFormData({ ...formData, image_url: e.target.value })}
                    placeholder="Enter image URL"
                    className="mb-2"
                  />
                  <p className="text-xs text-slate-500">
                    Paste an image URL or leave blank for default placeholder
                  </p>
                </div>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label>SKU *</Label>
                <Input
                  value={formData.sku}
                  onChange={(e) => setFormData({ ...formData, sku: e.target.value })}
                  placeholder="e.g., CEM-001"
                />
              </div>
              <div>
                <Label>Brand *</Label>
                <Input
                  value={formData.brand || ''}
                  onChange={(e) => setFormData({ ...formData, brand: e.target.value })}
                  placeholder="e.g., Aries, Sika"
                />
              </div>
            </div>

            {/* Barcode Field */}
            <div className="border rounded-lg p-4 bg-gradient-to-r from-blue-50 to-indigo-50">
              <div className="flex items-center gap-2 mb-3">
                <Barcode className="w-5 h-5 text-blue-600" />
                <Label className="font-semibold text-blue-900">Barcode</Label>
              </div>
              <div className="grid grid-cols-1 gap-3">
                <div className="flex gap-2">
                  <Input
                    value={formData.barcode || ''}
                    onChange={(e) => setFormData({ ...formData, barcode: e.target.value })}
                    placeholder="Enter barcode or generate automatically"
                    className="flex-1 font-mono text-lg"
                  />
                  <Button
                    type="button"
                    variant="outline"
                    onClick={() => {
                      const newBarcode = generateEAN13Barcode();
                      setFormData({ ...formData, barcode: newBarcode });
                      toast.success('Barcode generated successfully!');
                    }}
                    className="whitespace-nowrap"
                  >
                    <Barcode className="w-4 h-4 mr-2" />
                    Generate EAN-13
                  </Button>
                </div>
                <div className="flex gap-2">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => {
                      const newBarcode = generateSimpleBarcode();
                      setFormData({ ...formData, barcode: newBarcode });
                      toast.success('Simple barcode generated!');
                    }}
                    className="flex-1"
                  >
                    Generate Simple
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => {
                      if (!formData.sku) {
                        toast.error('Please enter SKU first');
                        return;
                      }
                      const newBarcode = generateBarcodeFromSKU(formData.sku);
                      setFormData({ ...formData, barcode: newBarcode });
                      toast.success('Barcode generated from SKU!');
                    }}
                    className="flex-1"
                    disabled={!formData.sku}
                  >
                    From SKU
                  </Button>
                  {formData.barcode && (
                    <Button
                      type="button"
                      variant="ghost"
                      size="sm"
                      onClick={() => {
                        setFormData({ ...formData, barcode: '' });
                        toast.info('Barcode cleared');
                      }}
                      className="text-red-600 hover:text-red-700 hover:bg-red-50"
                    >
                      <X className="w-4 h-4" />
                    </Button>
                  )}
                </div>
                {formData.barcode && (
                  <div className="mt-2 p-3 bg-white rounded border">
                    <div className="flex items-center justify-between">
                      <div>
                        <div className="text-xs text-slate-500 mb-1">Generated Barcode:</div>
                        <div className="font-mono text-2xl font-bold text-slate-900 tracking-wider">
                          {formatBarcodeDisplay(formData.barcode)}
                        </div>
                        {formData.barcode.length === 13 && (
                          <div className="text-xs mt-1">
                            {validateEAN13Barcode(formData.barcode) ? (
                              <span className="text-green-600 flex items-center gap-1">
                                <CheckCircle className="w-3 h-3" /> Valid EAN-13
                              </span>
                            ) : (
                              <span className="text-red-600 flex items-center gap-1">
                                <AlertTriangle className="w-3 h-3" /> Invalid checksum
                              </span>
                            )}
                          </div>
                        )}
                      </div>
                      <div className="text-right">
                        <Barcode className="w-16 h-16 text-slate-300" />
                      </div>
                    </div>
                  </div>
                )}
                <p className="text-xs text-blue-700">
                  💡 <strong>EAN-13:</strong> Standard 13-digit barcode • <strong>Simple:</strong> Custom TB-YYYYMMDD-XXXXX format • <strong>From SKU:</strong> Convert SKU to barcode
                </p>
              </div>
            </div>

            <div>
              <Label>Product Name *</Label>
              <Input
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                placeholder="e.g., Semen Portland - Gresik"
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label>Category *</Label>
                <Select 
                  value={formData.category_id} 
                  onValueChange={(v) => {
                    setFormData({ ...formData, category_id: v, sub_category: '' });
                  }}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select category" />
                  </SelectTrigger>
                  <SelectContent>
                    {categories.map(cat => (
                      <SelectItem key={cat.id} value={cat.id}>{cat.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label>Sub-Category</Label>
                {(() => {
                  const selectedCategory = categories.find(c => c.id === formData.category_id);
                  const hasSubCategories = selectedCategory?.sub_categories && selectedCategory.sub_categories.length > 0;
                  
                  return hasSubCategories ? (
                    <Select 
                      value={formData.sub_category || ''} 
                      onValueChange={(v) => setFormData({ ...formData, sub_category: v })}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Select sub-category" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="">None</SelectItem>
                        {selectedCategory.sub_categories!.map(subCat => (
                          <SelectItem key={subCat} value={subCat}>{subCat}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  ) : (
                    <Input
                      value={formData.sub_category || ''}
                      onChange={(e) => setFormData({ ...formData, sub_category: e.target.value })}
                      placeholder="Enter sub-category (optional)"
                      disabled={!formData.category_id}
                    />
                  );
                })()}
              </div>
            </div>

            <div>
              <Label>Supplier *</Label>
              <Select value={formData.supplier_id} onValueChange={(v) => setFormData({ ...formData, supplier_id: v })}>
                <SelectTrigger>
                  <SelectValue placeholder="Select supplier" />
                </SelectTrigger>
                <SelectContent>
                  {suppliers.map(sup => (
                    <SelectItem key={sup.id} value={sup.id}>{sup.name}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div>
              <Label>Description</Label>
              <Textarea
                value={formData.description}
                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                placeholder="Product description"
                rows={2}
              />
            </div>

            <div>
              <Label>Base Unit</Label>
              <Input
                value={formData.base_unit}
                onChange={(e) => setFormData({ ...formData, base_unit: e.target.value })}
                placeholder="e.g., kg, piece"
              />
              <p className="text-xs text-slate-500 mt-1">
                The smallest unit for this product (e.g., kg, piece, liter)
              </p>
            </div>

            <div className="grid grid-cols-3 gap-4">
              <div>
                <Label>Current Stock</Label>
                <Input
                  type="number"
                  value={formData.current_stock}
                  onChange={(e) => setFormData({ ...formData, current_stock: Number(e.target.value) })}
                />
              </div>
              <div>
                <Label>Min Stock</Label>
                <Input
                  type="number"
                  value={formData.min_stock}
                  onChange={(e) => setFormData({ ...formData, min_stock: Number(e.target.value) })}
                />
              </div>
              <div>
                <Label>Max Stock</Label>
                <Input
                  type="number"
                  value={formData.max_stock}
                  onChange={(e) => setFormData({ ...formData, max_stock: Number(e.target.value) })}
                />
              </div>
            </div>

            <div className="border-t pt-4">
              <h4 className="font-medium mb-3">Pricing Configuration</h4>
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <Label>Last Purchase Date</Label>
                  <Input
                    type="date"
                    value={formData.last_purchase_date ? new Date(formData.last_purchase_date).toISOString().split('T')[0] : ''}
                    onChange={(e) => setFormData({ ...formData, last_purchase_date: e.target.value ? new Date(e.target.value).toISOString() : undefined })}
                  />
                  <p className="text-xs text-slate-500 mt-1">
                    Date when this product was last purchased
                  </p>
                </div>
                <div>
                  <Label>Last Purchase Price</Label>
                  <Input
                    type="number"
                    value={formData.last_purchase_price || 0}
                    onChange={(e) => setFormData({ ...formData, last_purchase_price: Number(e.target.value) })}
                  />
                  <p className="text-xs text-slate-500 mt-1">
                    Price per {formData.base_unit} from last purchase
                  </p>
                </div>
              </div>
              <div className="grid grid-cols-3 gap-4">
                <div>
                  <Label>HPP (COGS) *</Label>
                  <Input
                    type="number"
                    value={formData.hpp}
                    onChange={(e) => handleHppChange(Number(e.target.value))}
                  />
                  <p className="text-xs text-slate-500 mt-1">
                    Current cost price
                  </p>
                </div>
                <div>
                  <Label>Margin % *</Label>
                  <Input
                    type="number"
                    value={formData.margin_percentage}
                    onChange={(e) => handleMarginChange(Number(e.target.value))}
                  />
                  <p className="text-xs text-slate-500 mt-1">
                    Auto-calculates selling price
                  </p>
                </div>
                <div>
                  <Label>Selling Price *</Label>
                  <Input
                    type="number"
                    value={formData.selling_price}
                    onChange={(e) => handleSellingPriceChange(Number(e.target.value))}
                  />
                  <p className="text-xs text-slate-500 mt-1">
                    Auto-calculated
                  </p>
                </div>
              </div>
              <div className="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <p className="text-sm text-blue-900 font-medium">
                  💡 Pricing Formula: Selling Price = HPP × (1 + Margin%)
                </p>
                <p className="text-xs text-blue-700 mt-1">
                  Change either HPP or Margin% to auto-update selling price. Or edit selling price directly to auto-calculate margin.
                </p>
              </div>
            </div>

            <DialogFooter>
              <Button variant="outline" onClick={() => setShowDialog(false)}>
                Cancel
              </Button>
              <Button onClick={handleSubmit}>
                {editingProduct ? 'Update Product' : 'Add Product'}
              </Button>
            </DialogFooter>
          </div>
        </DialogContent>
      </Dialog>

      {/* Product Preview Dialog */}
      <Dialog open={showPreviewDialog} onOpenChange={setShowPreviewDialog}>
        <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>Product Details</DialogTitle>
            <DialogDescription>
              Complete information about this product
            </DialogDescription>
          </DialogHeader>

          {previewProduct && (() => {
            const category = categories.find(c => c.id === previewProduct.category_id);
            const supplier = suppliers.find(s => s.id === previewProduct.supplier_id);
            const status = getStockStatus(previewProduct);
            const StatusIcon = status.icon;

            return (
              <div className="space-y-6">
                {/* Product Image and Basic Info */}
                <div className="flex gap-6">
                  <div className="flex-shrink-0">
                    <div className="w-48 h-48 rounded-lg overflow-hidden border-2 border-slate-200 bg-slate-50">
                      <img
                        src={previewProduct.image_url || 'https://via.placeholder.com/200?text=No+Image'}
                        alt={previewProduct.name}
                        className="w-full h-full object-cover"
                        onError={(e) => {
                          e.currentTarget.src = 'https://via.placeholder.com/200?text=No+Image';
                        }}
                      />
                    </div>
                  </div>
                  <div className="flex-1 space-y-3">
                    <div>
                      <Badge variant="secondary" className="mb-2">
                        {previewProduct.brand || 'No Brand'}
                      </Badge>
                      <h3 className="text-2xl font-bold text-slate-900">{previewProduct.name}</h3>
                      <p className="text-sm text-slate-500 font-mono mt-1">SKU: {previewProduct.sku}</p>
                    </div>
                    <div className="flex gap-2 flex-wrap">
                      <Badge variant="outline">{category?.name}</Badge>
                      {previewProduct.sub_category && (
                        <Badge variant="outline" className="bg-blue-50 text-blue-700 border-blue-200">
                          {previewProduct.sub_category}
                        </Badge>
                      )}
                      <Badge variant={status.color as any} className="flex items-center gap-1 w-fit">
                        <StatusIcon className="w-3 h-3" />
                        {status.label}
                      </Badge>
                    </div>
                  </div>
                </div>

                {/* Description */}
                {previewProduct.description && (
                  <div className="border-t pt-4">
                    <h4 className="font-semibold text-slate-900 mb-2">Description</h4>
                    <p className="text-slate-600">{previewProduct.description}</p>
                  </div>
                )}

                {/* Stock Information */}
                <div className="border-t pt-4">
                  <h4 className="font-semibold text-slate-900 mb-3">Stock Information</h4>
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <Card>
                      <CardContent className="pt-4 pb-4">
                        <div className="text-xs text-slate-500 mb-1">Current Stock</div>
                        <div className="text-2xl font-bold text-slate-900">
                          {previewProduct.current_stock.toLocaleString()}
                        </div>
                        <div className="text-xs text-slate-500">{previewProduct.base_unit}</div>
                      </CardContent>
                    </Card>
                    <Card>
                      <CardContent className="pt-4 pb-4">
                        <div className="text-xs text-slate-500 mb-1">Min Stock</div>
                        <div className="text-2xl font-bold text-orange-600">
                          {previewProduct.min_stock.toLocaleString()}
                        </div>
                        <div className="text-xs text-slate-500">{previewProduct.base_unit}</div>
                      </CardContent>
                    </Card>
                    <Card>
                      <CardContent className="pt-4 pb-4">
                        <div className="text-xs text-slate-500 mb-1">Max Stock</div>
                        <div className="text-2xl font-bold text-blue-600">
                          {previewProduct.max_stock.toLocaleString()}
                        </div>
                        <div className="text-xs text-slate-500">{previewProduct.base_unit}</div>
                      </CardContent>
                    </Card>
                    <Card>
                      <CardContent className="pt-4 pb-4">
                        <div className="text-xs text-slate-500 mb-1">Base Unit</div>
                        <div className="text-2xl font-bold text-slate-900">
                          {previewProduct.base_unit}
                        </div>
                        <div className="text-xs text-slate-500">smallest unit</div>
                      </CardContent>
                    </Card>
                  </div>
                </div>

                {/* Unit Conversions */}
                {previewProduct.units && previewProduct.units.length > 0 && (
                  <div className="border-t pt-4">
                    <h4 className="font-semibold text-slate-900 mb-3">Unit Conversions</h4>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                      {previewProduct.units.map((unit, idx) => (
                        <div key={idx} className="border rounded-lg p-3 bg-slate-50">
                          <div className="flex justify-between items-center">
                            <div>
                              <div className="font-medium text-slate-900 capitalize">{unit.unit_name}</div>
                              <div className="text-sm text-slate-500">
                                = {unit.conversion_to_base} {previewProduct.base_unit}
                              </div>
                            </div>
                            <div className="flex gap-1">
                              {unit.is_default_purchase && (
                                <Badge variant="outline" className="text-xs">Purchase</Badge>
                              )}
                              {unit.is_default_sale && (
                                <Badge variant="outline" className="text-xs">Sale</Badge>
                              )}
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                {/* Pricing Information */}
                <div className="border-t pt-4">
                  <h4 className="font-semibold text-slate-900 mb-3">Pricing Information</h4>
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <Card>
                      <CardContent className="pt-4 pb-4">
                        <div className="text-xs text-slate-500 mb-1">HPP (COGS)</div>
                        <div className="text-xl font-bold text-slate-900">
                          Rp {previewProduct.hpp.toLocaleString()}
                        </div>
                        <div className="text-xs text-slate-500">per {previewProduct.base_unit}</div>
                      </CardContent>
                    </Card>
                    <Card>
                      <CardContent className="pt-4 pb-4">
                        <div className="text-xs text-slate-500 mb-1">Selling Price</div>
                        <div className="text-xl font-bold text-green-600">
                          Rp {previewProduct.selling_price.toLocaleString()}
                        </div>
                        <div className="text-xs text-slate-500">per {previewProduct.base_unit}</div>
                      </CardContent>
                    </Card>
                    <Card>
                      <CardContent className="pt-4 pb-4">
                        <div className="text-xs text-slate-500 mb-1">Margin</div>
                        <div className="text-xl font-bold text-blue-600">
                          {previewProduct.margin_percentage.toFixed(1)}%
                        </div>
                        <div className="text-xs text-slate-500">profit margin</div>
                      </CardContent>
                    </Card>
                    <Card>
                      <CardContent className="pt-4 pb-4">
                        <div className="text-xs text-slate-500 mb-1">Tax</div>
                        <div className="text-xl font-bold text-slate-900">
                          {previewProduct.tax_percentage}%
                        </div>
                        <div className="text-xs text-slate-500">tax rate</div>
                      </CardContent>
                    </Card>
                  </div>
                </div>

                {/* Supplier Information */}
                <div className="border-t pt-4">
                  <h4 className="font-semibold text-slate-900 mb-3">Supplier Information</h4>
                  {supplier && (
                    <div className="border rounded-lg p-4 bg-slate-50">
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <div className="text-sm text-slate-500">Supplier Name</div>
                          <div className="font-medium text-slate-900">{supplier.name}</div>
                        </div>
                        <div>
                          <div className="text-sm text-slate-500">Contact Person</div>
                          <div className="font-medium text-slate-900">{supplier.contact_person}</div>
                        </div>
                        <div>
                          <div className="text-sm text-slate-500">Phone</div>
                          <div className="font-medium text-slate-900">{supplier.phone}</div>
                        </div>
                        <div>
                          <div className="text-sm text-slate-500">Email</div>
                          <div className="font-medium text-slate-900">{supplier.email}</div>
                        </div>
                        <div className="md:col-span-2">
                          <div className="text-sm text-slate-500">Address</div>
                          <div className="font-medium text-slate-900">{supplier.address}</div>
                        </div>
                      </div>
                    </div>
                  )}
                </div>

                {/* Metadata */}
                <div className="border-t pt-4 text-xs text-slate-500">
                  <div className="flex justify-between">
                    <span>Created: {new Date(previewProduct.created_at).toLocaleString()}</span>
                    <span>Updated: {new Date(previewProduct.updated_at).toLocaleString()}</span>
                  </div>
                </div>
              </div>
            );
          })()}

          <DialogFooter>
            <Button variant="outline" onClick={() => setShowPreviewDialog(false)}>
              Close
            </Button>
            {hasPermission('products.edit') && previewProduct && (
              <Button onClick={() => {
                setShowPreviewDialog(false);
                handleOpenDialog(previewProduct);
              }}>
                <Edit className="w-4 h-4 mr-2" />
                Edit Product
              </Button>
            )}
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
};