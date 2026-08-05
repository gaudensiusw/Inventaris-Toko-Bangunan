import React, { useState } from 'react';
import { useInventory } from '../contexts/InventoryContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Badge } from '../components/ui/badge';
import { Input } from '../components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../components/ui/table';
import { History, Search, FileEdit, DollarSign, Package, Users, Filter } from 'lucide-react';

export const AuditLogsPage: React.FC = () => {
  const { auditLogs, products } = useInventory();
  const [searchTerm, setSearchTerm] = useState('');
  const [filterType, setFilterType] = useState('all');
  const [filterAction, setFilterAction] = useState('all');

  const filteredLogs = auditLogs.filter(log => {
    const matchesSearch = 
      log.changed_by.toLowerCase().includes(searchTerm.toLowerCase()) ||
      log.reason.toLowerCase().includes(searchTerm.toLowerCase()) ||
      log.entity_id.toLowerCase().includes(searchTerm.toLowerCase());
    
    const matchesType = filterType === 'all' || log.entity_type === filterType;
    const matchesAction = filterAction === 'all' || log.action === filterAction;
    
    return matchesSearch && matchesType && matchesAction;
  });

  const sortedLogs = [...filteredLogs].sort((a, b) => 
    new Date(b.changed_at).getTime() - new Date(a.changed_at).getTime()
  );

  const getEntityIcon = (type: string) => {
    switch (type) {
      case 'product':
        return <Package className="w-4 h-4 text-blue-600" />;
      case 'price':
        return <DollarSign className="w-4 h-4 text-green-600" />;
      case 'stock':
        return <FileEdit className="w-4 h-4 text-orange-600" />;
      case 'supplier':
        return <Users className="w-4 h-4 text-purple-600" />;
      default:
        return <History className="w-4 h-4" />;
    }
  };

  const getActionBadge = (action: string) => {
    const variants: any = {
      create: 'default',
      update: 'secondary',
      delete: 'destructive',
      adjust: 'default',
    };
    return variants[action] || 'default';
  };

  const getEntityName = (entityType: string, entityId: string) => {
    if (entityType === 'product' || entityType === 'price' || entityType === 'stock') {
      const product = products.find(p => p.id === entityId);
      return product ? `${product.name} (${product.sku})` : entityId;
    }
    return entityId;
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900">Audit Logs</h2>
          <p className="text-slate-600">Complete history of all system changes</p>
        </div>
        <Badge variant="outline" className="w-fit">
          {sortedLogs.length} records
        </Badge>
      </div>

      {/* Info Card */}
      <Card className="border-blue-200 bg-blue-50">
        <CardContent className="pt-6">
          <div className="flex items-start gap-3">
            <History className="w-5 h-5 text-blue-600 mt-0.5" />
            <div>
              <h3 className="font-medium text-blue-900 mb-1">About Audit Trail</h3>
              <p className="text-sm text-blue-800">
                Every change to products, prices, and stock levels is tracked with <strong>WHO</strong> made the change, 
                <strong> WHEN</strong> it occurred, <strong>WHAT</strong> changed (before and after values), 
                and <strong>WHY</strong> (reason provided). This ensures complete accountability and traceability.
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Filters */}
      <Card>
        <CardContent className="pt-6">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="relative">
              <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
              <Input
                placeholder="Search logs..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
              />
            </div>
            
            <Select value={filterType} onValueChange={setFilterType}>
              <SelectTrigger>
                <SelectValue placeholder="Filter by entity type" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Types</SelectItem>
                <SelectItem value="product">Product</SelectItem>
                <SelectItem value="price">Price</SelectItem>
                <SelectItem value="stock">Stock</SelectItem>
                <SelectItem value="supplier">Supplier</SelectItem>
              </SelectContent>
            </Select>

            <Select value={filterAction} onValueChange={setFilterAction}>
              <SelectTrigger>
                <SelectValue placeholder="Filter by action" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Actions</SelectItem>
                <SelectItem value="create">Create</SelectItem>
                <SelectItem value="update">Update</SelectItem>
                <SelectItem value="delete">Delete</SelectItem>
                <SelectItem value="adjust">Adjust</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </CardContent>
      </Card>

      {/* Audit Logs Table */}
      <Card>
        <CardHeader>
          <CardTitle>Audit Trail ({sortedLogs.length} records)</CardTitle>
          <CardDescription>Comprehensive change log with full details</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Timestamp</TableHead>
                  <TableHead>Entity Type</TableHead>
                  <TableHead>Action</TableHead>
                  <TableHead>Entity</TableHead>
                  <TableHead>Field Changed</TableHead>
                  <TableHead>Old Value</TableHead>
                  <TableHead>New Value</TableHead>
                  <TableHead>Reason</TableHead>
                  <TableHead>Changed By</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {sortedLogs.map(log => (
                  <TableRow key={log.id}>
                    <TableCell className="text-sm">
                      {new Date(log.changed_at).toLocaleString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                      })}
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-2">
                        {getEntityIcon(log.entity_type)}
                        <span className="capitalize">{log.entity_type}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge variant={getActionBadge(log.action)}>
                        {log.action}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-sm">
                      {getEntityName(log.entity_type, log.entity_id)}
                    </TableCell>
                    <TableCell>
                      <code className="text-xs bg-slate-100 px-2 py-1 rounded">
                        {log.field_changed}
                      </code>
                    </TableCell>
                    <TableCell>
                      <span className="text-sm text-red-600 line-through">
                        {log.old_value || 'N/A'}
                      </span>
                    </TableCell>
                    <TableCell>
                      <span className="text-sm text-green-600 font-medium">
                        {log.new_value || 'N/A'}
                      </span>
                    </TableCell>
                    <TableCell className="text-sm max-w-xs">
                      {log.reason}
                    </TableCell>
                    <TableCell>
                      <div className="text-sm">
                        <p className="font-medium">{log.changed_by}</p>
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      {/* Summary Stats */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <Package className="w-5 h-5 text-blue-600" />
              </div>
              <div>
                <p className="text-sm text-slate-600">Product Changes</p>
                <p className="text-xl font-bold">
                  {auditLogs.filter(l => l.entity_type === 'product').length}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                <DollarSign className="w-5 h-5 text-green-600" />
              </div>
              <div>
                <p className="text-sm text-slate-600">Price Updates</p>
                <p className="text-xl font-bold">
                  {auditLogs.filter(l => l.entity_type === 'price').length}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                <FileEdit className="w-5 h-5 text-orange-600" />
              </div>
              <div>
                <p className="text-sm text-slate-600">Stock Adjustments</p>
                <p className="text-xl font-bold">
                  {auditLogs.filter(l => l.entity_type === 'stock').length}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <Users className="w-5 h-5 text-purple-600" />
              </div>
              <div>
                <p className="text-sm text-slate-600">Supplier Changes</p>
                <p className="text-xl font-bold">
                  {auditLogs.filter(l => l.entity_type === 'supplier').length}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};
