import React from 'react';
import { Outlet, Link, useLocation, useNavigate } from 'react-router';
import { useAuth } from '../contexts/AuthContext';
import { Button } from '../components/ui/button';
import { ScrollArea } from '../components/ui/scroll-area';
import { Badge } from '../components/ui/badge';
import {
  Building2,
  LayoutDashboard,
  Package,
  ShoppingCart,
  TrendingUp,
  Settings,
  LogOut,
  Bell,
  Users,
  FileText,
  BarChart3,
  DollarSign,
  Brain,
  History,
  AlertTriangle,
  Menu,
  X,
  PackageOpen,
  UserCheck,
  ClipboardCheck,
} from 'lucide-react';
import { useInventory } from '../contexts/InventoryContext';
import { cn } from '../components/ui/utils';

export const DashboardLayout: React.FC = () => {
  const { user, logout, hasPermission } = useAuth();
  const { stockNotifications } = useInventory();
  const location = useLocation();
  const navigate = useNavigate();
  const [sidebarOpen, setSidebarOpen] = React.useState(false);

  // Check authentication
  React.useEffect(() => {
    if (!user) {
      navigate('/login');
    }
  }, [user, navigate]);

  if (!user) {
    return null;
  }

  const unreadNotifications = stockNotifications.filter(n => !n.is_read).length;

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const menuItems = [
    {
      title: 'Dashboard',
      icon: LayoutDashboard,
      path: '/dashboard',
      permission: null,
    },
    {
      title: 'POS / Cashier',
      icon: ShoppingCart,
      path: '/dashboard/pos',
      permission: 'pos.access',
    },
    {
      title: 'Products',
      icon: Package,
      path: '/dashboard/products',
      permission: 'products.view',
    },
    {
      title: 'Tagihan Supplier',
      icon: DollarSign,
      path: '/dashboard/payables',
      permission: 'reports.financial',
    },
    {
      title: 'Stock Management',
      icon: TrendingUp,
      path: '/dashboard/stock',
      permission: 'stock.view',
    },
    {
      title: 'Stock Opname',
      icon: ClipboardCheck,
      path: '/dashboard/stock-opname',
      permission: 'stock.view',
    },
    {
      title: 'Suppliers',
      icon: Users,
      path: '/dashboard/suppliers',
      permission: 'suppliers.manage',
    },
    {
      title: 'Karyawan',
      icon: Users,
      path: '/dashboard/employees',
      permission: 'reports.financial',
    },
    {
      title: 'Pelanggan',
      icon: UserCheck,
      path: '/dashboard/customers',
      permission: 'reports.financial',
    },
    {
      title: 'Barang Operasional',
      icon: PackageOpen,
      path: '/dashboard/operational-items',
      permission: 'reports.financial',
    },
    {
      title: 'Notifications',
      icon: Bell,
      path: '/dashboard/notifications',
      permission: 'notifications.manage',
      badge: unreadNotifications > 0 ? unreadNotifications : null,
    },
    {
      title: 'Reports',
      icon: FileText,
      path: '/dashboard/reports',
      permission: 'reports.basic',
    },
    {
      title: 'Financial Reports',
      icon: DollarSign,
      path: '/dashboard/financial',
      permission: 'reports.financial',
    },
    {
      title: 'Pricing Engine',
      icon: BarChart3,
      path: '/dashboard/pricing',
      permission: 'pricing.manage',
    },
    {
      title: 'ML Predictions',
      icon: Brain,
      path: '/dashboard/predictions',
      permission: 'ml.predictions',
    },
    {
      title: 'Audit Logs',
      icon: History,
      path: '/dashboard/audit',
      permission: 'audit.view',
    },
  ];

  const visibleMenuItems = menuItems.filter(
    item => !item.permission || hasPermission(item.permission)
  );

  // Group menu items by section
  const mainMenuItems = visibleMenuItems.slice(0, 4);
  const managementMenuItems = visibleMenuItems.slice(4, 11);
  const reportsMenuItems = visibleMenuItems.slice(11);

  return (
    <div className="flex h-screen bg-slate-50">
      {/* Sidebar */}
      <aside
        className={cn(
          'fixed inset-y-0 left-0 z-50 w-64 bg-[#0f172a] text-white transform transition-transform duration-300 lg:relative lg:translate-x-0',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        )}
      >
        <div className="flex flex-col h-full">
          {/* Logo */}
          <div className="flex items-center justify-between px-6 py-4 border-b border-slate-700/50">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <Building2 className="w-6 h-6" />
              </div>
              <div>
                <h2 className="font-bold">Toko Bangunan</h2>
                <p className="text-xs text-slate-400">IMS v1.0</p>
              </div>
            </div>
            <Button
              variant="ghost"
              size="icon"
              className="lg:hidden flex-shrink-0"
              onClick={() => setSidebarOpen(false)}
            >
              <X className="w-5 h-5" />
            </Button>
          </div>

          {/* User Info */}
          <div className="px-6 py-4 bg-slate-800/30 border-b border-slate-700/50">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                {user?.name.charAt(0).toUpperCase()}
              </div>
              <div className="flex-1 min-w-0">
                <p className="font-medium truncate">{user?.name}</p>
                <Badge
                  variant="secondary"
                  className={cn(
                    'text-xs',
                    user?.role === 'owner' && 'bg-yellow-500/20 text-yellow-300',
                    user?.role === 'supervisor' && 'bg-blue-500/20 text-blue-300',
                    user?.role === 'operator' && 'bg-green-500/20 text-green-300'
                  )}
                >
                  {user?.role}
                </Badge>
              </div>
            </div>
          </div>

          {/* Navigation */}
          <div className="flex-1 overflow-y-auto px-3 py-4">
            <nav className="space-y-1">
              {/* Main Section */}
              <div className="text-xs font-semibold text-slate-400 uppercase tracking-wider px-3 mt-4 mb-2">
                Main
              </div>
              {mainMenuItems.map((item) => {
                const Icon = item.icon;
                const isActive = location.pathname === item.path;

                return (
                  <Link
                    key={item.path}
                    to={item.path}
                    onClick={() => setSidebarOpen(false)}
                    className={cn(
                      'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors',
                      isActive
                        ? 'bg-blue-600 text-white'
                        : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'
                    )}
                  >
                    <Icon className="w-5 h-5 flex-shrink-0" />
                    <span className="flex-1">{item.title}</span>
                    {item.badge && (
                      <Badge className="bg-red-500 text-white text-xs flex-shrink-0">
                        {item.badge}
                      </Badge>
                    )}
                  </Link>
                );
              })}

              {/* Management Section */}
              {managementMenuItems.length > 0 && (
                <>
                  <div className="text-xs font-semibold text-slate-400 uppercase tracking-wider px-3 mt-4 mb-2">
                    Management
                  </div>
                  {managementMenuItems.map((item) => {
                    const Icon = item.icon;
                    const isActive = location.pathname === item.path;

                    return (
                      <Link
                        key={item.path}
                        to={item.path}
                        onClick={() => setSidebarOpen(false)}
                        className={cn(
                          'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors',
                          isActive
                            ? 'bg-blue-600 text-white'
                            : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'
                        )}
                      >
                        <Icon className="w-5 h-5 flex-shrink-0" />
                        <span className="flex-1">{item.title}</span>
                        {item.badge && (
                          <Badge className="bg-red-500 text-white text-xs flex-shrink-0">
                            {item.badge}
                          </Badge>
                        )}
                      </Link>
                    );
                  })}
                </>
              )}

              {/* Reports & Advanced Section */}
              {reportsMenuItems.length > 0 && (
                <>
                  <div className="text-xs font-semibold text-slate-400 uppercase tracking-wider px-3 mt-4 mb-2">
                    Reports & Advanced
                  </div>
                  {reportsMenuItems.map((item) => {
                    const Icon = item.icon;
                    const isActive = location.pathname === item.path;

                    return (
                      <Link
                        key={item.path}
                        to={item.path}
                        onClick={() => setSidebarOpen(false)}
                        className={cn(
                          'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors',
                          isActive
                            ? 'bg-blue-600 text-white'
                            : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'
                        )}
                      >
                        <Icon className="w-5 h-5 flex-shrink-0" />
                        <span className="flex-1">{item.title}</span>
                        {item.badge && (
                          <Badge className="bg-red-500 text-white text-xs flex-shrink-0">
                            {item.badge}
                          </Badge>
                        )}
                      </Link>
                    );
                  })}
                </>
              )}
            </nav>
          </div>

          {/* Logout */}
          <div className="px-3 py-4 border-t border-slate-700/50">
            <Button
              variant="ghost"
              className="w-full justify-start text-slate-300 hover:text-white hover:bg-slate-700/50 transition-colors"
              onClick={handleLogout}
            >
              <LogOut className="w-5 h-5 mr-3 flex-shrink-0" />
              Logout
            </Button>
          </div>
        </div>
      </aside>

      {/* Main Content */}
      <div className="flex-1 flex flex-col overflow-hidden">
        {/* Top Bar */}
        <header className="bg-white border-b border-slate-200 px-6 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <Button
                variant="ghost"
                size="icon"
                className="lg:hidden"
                onClick={() => setSidebarOpen(true)}
              >
                <Menu className="w-5 h-5" />
              </Button>
              <div>
                <h1 className="text-xl font-bold text-slate-900">
                  {visibleMenuItems.find(item => item.path === location.pathname)?.title || 'Dashboard'}
                </h1>
                <p className="text-sm text-slate-500">
                  {new Date().toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                  })}
                </p>
              </div>
            </div>

            <div className="flex items-center gap-3">
              <Link to="/dashboard/notifications">
                <Button variant="ghost" size="icon" className="relative">
                  <Bell className="w-5 h-5" />
                  {unreadNotifications > 0 && (
                    <span className="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full" />
                  )}
                </Button>
              </Link>
            </div>
          </div>
        </header>

        {/* Page Content */}
        <main className="flex-1 overflow-auto">
          <div className="p-6">
            <Outlet />
          </div>
        </main>
      </div>

      {/* Overlay for mobile */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 bg-black/50 z-40 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}
    </div>
  );
};