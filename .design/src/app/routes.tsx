import { createBrowserRouter, Navigate } from 'react-router';
import { RootLayout } from './layouts/RootLayout';
import { DashboardLayout } from './layouts/DashboardLayout';
import { LoginPage } from './pages/LoginPage';
import { DashboardOverview } from './pages/DashboardOverview';
import { POSPage } from './pages/POSPage';
import { ProductsPage } from './pages/ProductsPage';
import { StockManagementPage } from './pages/StockManagementPage';
import { SuppliersPage } from './pages/SuppliersPage';
import { NotificationsPage } from './pages/NotificationsPage';
import { ReportsPage } from './pages/ReportsPage';
import { FinancialReportsPage } from './pages/FinancialReportsPage';
import { PricingEnginePage } from './pages/PricingEnginePage';
import { MLPredictionsPage } from './pages/MLPredictionsPage';
import { AuditLogsPage } from './pages/AuditLogsPage';
import { PayablesPage } from './pages/PayablesPage';
import { EmployeesPage } from './pages/EmployeesPage';
import { OperationalItemsPage } from './pages/OperationalItemsPage';
import { CustomersPage } from './pages/CustomersPage';
import { StockOpnamePage } from './pages/StockOpnamePage';

export const router = createBrowserRouter([
  {
    path: '/',
    element: <RootLayout />,
    children: [
      {
        index: true,
        element: <Navigate to="/dashboard" replace />,
      },
      {
        path: 'login',
        element: <LoginPage />,
      },
      {
        path: 'dashboard',
        element: <DashboardLayout />,
        children: [
          {
            index: true,
            element: <DashboardOverview />,
          },
          {
            path: 'pos',
            element: <POSPage />,
          },
          {
            path: 'products',
            element: <ProductsPage />,
          },
          {
            path: 'stock',
            element: <StockManagementPage />,
          },
          {
            path: 'suppliers',
            element: <SuppliersPage />,
          },
          {
            path: 'notifications',
            element: <NotificationsPage />,
          },
          {
            path: 'reports',
            element: <ReportsPage />,
          },
          {
            path: 'financial',
            element: <FinancialReportsPage />,
          },
          {
            path: 'pricing',
            element: <PricingEnginePage />,
          },
          {
            path: 'predictions',
            element: <MLPredictionsPage />,
          },
          {
            path: 'audit',
            element: <AuditLogsPage />,
          },
          {
            path: 'payables',
            element: <PayablesPage />,
          },
          {
            path: 'employees',
            element: <EmployeesPage />,
          },
          {
            path: 'operational-items',
            element: <OperationalItemsPage />,
          },
          {
            path: 'customers',
            element: <CustomersPage />,
          },
          {
            path: 'stock-opname',
            element: <StockOpnamePage />,
          },
        ],
      },
    ],
  },
]);