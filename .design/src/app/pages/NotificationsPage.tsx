import React from 'react';
import { useInventory } from '../contexts/InventoryContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import { Bell, AlertTriangle, TrendingUp, CheckCircle, X } from 'lucide-react';

export const NotificationsPage: React.FC = () => {
  const { stockNotifications, markNotificationRead, products } = useInventory();

  const unreadNotifications = stockNotifications.filter(n => !n.is_read);
  const readNotifications = stockNotifications.filter(n => n.is_read);

  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case 'high':
        return 'border-red-200 bg-red-50';
      case 'medium':
        return 'border-orange-200 bg-orange-50';
      case 'low':
        return 'border-blue-200 bg-blue-50';
      default:
        return 'border-slate-200 bg-slate-50';
    }
  };

  const getSeverityIcon = (severity: string) => {
    switch (severity) {
      case 'high':
        return <AlertTriangle className="w-5 h-5 text-red-600" />;
      case 'medium':
        return <Bell className="w-5 h-5 text-orange-600" />;
      case 'low':
        return <TrendingUp className="w-5 h-5 text-blue-600" />;
      default:
        return <Bell className="w-5 h-5" />;
    }
  };

  const NotificationCard = ({ notification, isRead }: any) => {
    const product = products.find(p => p.id === notification.product_id);
    
    return (
      <Card className={`${getSeverityColor(notification.severity)} ${!isRead ? 'border-2' : ''}`}>
        <CardContent className="pt-6">
          <div className="flex items-start justify-between">
            <div className="flex items-start gap-3 flex-1">
              {getSeverityIcon(notification.severity)}
              <div className="flex-1">
                <div className="flex items-center gap-2 mb-1">
                  <h4 className="font-medium">{notification.notification_type.replace('_', ' ').toUpperCase()}</h4>
                  <Badge variant={notification.severity === 'high' ? 'destructive' : 'secondary'}>
                    {notification.severity}
                  </Badge>
                  {!isRead && <Badge>New</Badge>}
                </div>
                <p className="text-sm text-slate-700 mb-2">{notification.message}</p>
                {product && (
                  <div className="text-xs text-slate-600 space-y-1">
                    <p><strong>Product:</strong> {product.name} ({product.sku})</p>
                    <p><strong>Current Stock:</strong> {product.current_stock} / Min: {product.min_stock}</p>
                  </div>
                )}
                <p className="text-xs text-slate-500 mt-2">
                  {new Date(notification.created_at).toLocaleString('id-ID')}
                </p>
              </div>
            </div>
            {!isRead && (
              <Button
                variant="ghost"
                size="icon"
                onClick={() => markNotificationRead(notification.id)}
              >
                <CheckCircle className="w-5 h-5 text-green-600" />
              </Button>
            )}
          </div>
        </CardContent>
      </Card>
    );
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900">Notifications</h2>
          <p className="text-slate-600">Stock alerts and system notifications</p>
        </div>
        <Badge variant="destructive" className="w-fit">
          {unreadNotifications.length} Unread
        </Badge>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Alerts</CardTitle>
            <Bell className="h-4 w-4 text-blue-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stockNotifications.length}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Unread</CardTitle>
            <AlertTriangle className="h-4 w-4 text-red-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{unreadNotifications.length}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">High Priority</CardTitle>
            <AlertTriangle className="h-4 w-4 text-orange-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {stockNotifications.filter(n => n.severity === 'high').length}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Unread Notifications */}
      {unreadNotifications.length > 0 && (
        <div>
          <h3 className="text-lg font-semibold mb-4">Unread Notifications</h3>
          <div className="space-y-3">
            {unreadNotifications.map(notification => (
              <NotificationCard key={notification.id} notification={notification} isRead={false} />
            ))}
          </div>
        </div>
      )}

      {/* Read Notifications */}
      {readNotifications.length > 0 && (
        <div>
          <h3 className="text-lg font-semibold mb-4">Read Notifications</h3>
          <div className="space-y-3">
            {readNotifications.map(notification => (
              <NotificationCard key={notification.id} notification={notification} isRead={true} />
            ))}
          </div>
        </div>
      )}

      {stockNotifications.length === 0 && (
        <Card>
          <CardContent className="pt-6 text-center py-12">
            <CheckCircle className="w-12 h-12 mx-auto text-green-600 mb-3" />
            <h3 className="text-lg font-medium mb-2">All Clear!</h3>
            <p className="text-slate-600">No notifications at this time</p>
          </CardContent>
        </Card>
      )}
    </div>
  );
};
