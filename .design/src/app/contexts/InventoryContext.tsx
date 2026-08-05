import React, { createContext, useContext, useState, ReactNode } from 'react';
import {
  Product,
  Category,
  Supplier,
  StockTransaction,
  AuditLog,
  StockNotification,
  Sale,
  Employee,
  Attendance,
  Payroll,
  OperationalItem,
  OperationalExpense,
  Customer,
  CustomerTransaction,
  CustomerPayment,
  products as initialProducts,
  categories as initialCategories,
  suppliers as initialSuppliers,
  stockTransactions as initialTransactions,
  auditLogs as initialAuditLogs,
  stockNotifications as initialNotifications,
  sales as initialSales,
  employees as initialEmployees,
  attendances as initialAttendances,
  payrolls as initialPayrolls,
  operationalItems as initialOperationalItems,
  operationalExpenses as initialOperationalExpenses,
  customers as initialCustomers,
  customerTransactions as initialCustomerTransactions,
} from '../data/mockData';

interface InventoryContextType {
  products: Product[];
  categories: Category[];
  suppliers: Supplier[];
  stockTransactions: StockTransaction[];
  auditLogs: AuditLog[];
  stockNotifications: StockNotification[];
  sales: Sale[];
  employees: Employee[];
  attendances: Attendance[];
  payrolls: Payroll[];
  operationalItems: OperationalItem[];
  operationalExpenses: OperationalExpense[];
  customers: Customer[];
  customerTransactions: CustomerTransaction[];
  
  // Product operations
  addProduct: (product: Omit<Product, 'id' | 'created_at' | 'updated_at'>) => void;
  updateProduct: (id: string, updates: Partial<Product>, reason: string, changedBy: string) => void;
  deleteProduct: (id: string) => void;
  
  // Stock operations
  addStockTransaction: (transaction: Omit<StockTransaction, 'id' | 'created_at'>) => void;
  adjustStock: (productId: string, quantity: number, reason: string, changedBy: string) => void;
  
  // Price operations
  updatePrice: (productId: string, newPrice: number, reason: string, changedBy: string) => void;
  
  // Notification operations
  markNotificationRead: (notificationId: string) => void;
  checkStockLevels: () => void;
  
  // Sale operations
  addSale: (sale: Omit<Sale, 'id' | 'created_at'>) => void;
  
  // Supplier operations
  addSupplier: (supplier: Omit<Supplier, 'id' | 'created_at'>) => void;
  updateSupplier: (id: string, updates: Partial<Supplier>) => void;
  
  // Category operations
  addCategory: (category: Omit<Category, 'id' | 'created_at'>) => void;

  // Employee operations
  addEmployee: (employee: Omit<Employee, 'id' | 'created_at'>) => void;
  updateEmployee: (id: string, updates: Partial<Employee>) => void;
  deleteEmployee: (id: string) => void;

  // Attendance operations
  addAttendance: (attendance: Omit<Attendance, 'id' | 'created_at'>) => void;
  updateAttendance: (id: string, updates: Partial<Attendance>) => void;
  deleteAttendance: (id: string) => void;

  // Payroll operations
  addPayroll: (payroll: Omit<Payroll, 'id' | 'created_at'>) => void;
  updatePayroll: (id: string, updates: Partial<Payroll>) => void;
  deletePayroll: (id: string) => void;

  // Operational Item operations
  addOperationalItem: (item: Omit<OperationalItem, 'id' | 'created_at' | 'updated_at'>) => void;
  updateOperationalItem: (id: string, updates: Partial<OperationalItem>) => void;
  deleteOperationalItem: (id: string) => void;

  // Operational Expense operations
  addOperationalExpense: (expense: Omit<OperationalExpense, 'id' | 'created_at'>) => void;
  deleteOperationalExpense: (id: string) => void;

  // Customer operations
  addCustomer: (customer: Omit<Customer, 'id' | 'created_at'>) => void;
  updateCustomer: (id: string, updates: Partial<Customer>) => void;
  deleteCustomer: (id: string) => void;

  // Customer Transaction operations
  addCustomerTransaction: (transaction: Omit<CustomerTransaction, 'id' | 'created_at' | 'updated_at'>) => void;
  updateCustomerTransaction: (id: string, updates: Partial<CustomerTransaction>) => void;
  addCustomerPayment: (transactionId: string, payment: Omit<CustomerPayment, 'id' | 'created_at'>) => void;
}

const InventoryContext = createContext<InventoryContextType | undefined>(undefined);

export const useInventory = () => {
  const context = useContext(InventoryContext);
  if (!context) {
    throw new Error('useInventory must be used within an InventoryProvider');
  }
  return context;
};

interface InventoryProviderProps {
  children: ReactNode;
}

export const InventoryProvider: React.FC<InventoryProviderProps> = ({ children }) => {
  const [products, setProducts] = useState<Product[]>(initialProducts);
  const [categories] = useState<Category[]>(initialCategories);
  const [suppliers, setSuppliers] = useState<Supplier[]>(initialSuppliers);
  const [stockTransactions, setStockTransactions] = useState<StockTransaction[]>(initialTransactions);
  const [auditLogs, setAuditLogs] = useState<AuditLog[]>(initialAuditLogs);
  const [stockNotifications, setStockNotifications] = useState<StockNotification[]>(initialNotifications);
  const [sales, setSales] = useState<Sale[]>(initialSales);
  const [employees, setEmployees] = useState<Employee[]>(initialEmployees);
  const [attendances, setAttendances] = useState<Attendance[]>(initialAttendances);
  const [payrolls, setPayrolls] = useState<Payroll[]>(initialPayrolls);
  const [operationalItems, setOperationalItems] = useState<OperationalItem[]>(initialOperationalItems);
  const [operationalExpenses, setOperationalExpenses] = useState<OperationalExpense[]>(initialOperationalExpenses);
  const [customers, setCustomers] = useState<Customer[]>(initialCustomers);
  const [customerTransactions, setCustomerTransactions] = useState<CustomerTransaction[]>(initialCustomerTransactions);

  const addProduct = (product: Omit<Product, 'id' | 'created_at' | 'updated_at'>) => {
    const newProduct: Product = {
      ...product,
      id: `prod-${Date.now()}`,
      units: product.units || [], // Ensure units array exists
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
    };
    setProducts(prev => [...prev, newProduct]);
  };

  const updateProduct = (id: string, updates: Partial<Product>, reason: string, changedBy: string) => {
    setProducts(prev =>
      prev.map(p => {
        if (p.id === id) {
          // Create audit logs for each changed field
          Object.keys(updates).forEach(key => {
            const field = key as keyof Product;
            if (p[field] !== updates[field] && updates[field] !== undefined) {
              const newLog: AuditLog = {
                id: `log-${Date.now()}-${key}`,
                entity_type: 'product',
                entity_id: id,
                action: 'update',
                field_changed: key,
                old_value: String(p[field]),
                new_value: String(updates[field]),
                reason,
                changed_by: changedBy,
                changed_at: new Date().toISOString(),
              };
              setAuditLogs(logs => [...logs, newLog]);
            }
          });

          return { ...p, ...updates, updated_at: new Date().toISOString() };
        }
        return p;
      })
    );
  };

  const deleteProduct = (id: string) => {
    setProducts(prev => prev.filter(p => p.id !== id));
  };

  const addStockTransaction = (transaction: Omit<StockTransaction, 'id' | 'created_at'>) => {
    const newTransaction: StockTransaction = {
      ...transaction,
      id: `txn-${Date.now()}`,
      unit_used: transaction.unit_used || 'piece', // Default unit
      quantity_in_unit: transaction.quantity_in_unit || transaction.quantity, // Default to base quantity
      created_at: new Date().toISOString(),
    };
    
    setStockTransactions(prev => [...prev, newTransaction]);

    // Update product stock
    setProducts(prev =>
      prev.map(p => {
        if (p.id === transaction.product_id) {
          const newStock = p.current_stock + transaction.quantity;
          return { ...p, current_stock: newStock, updated_at: new Date().toISOString() };
        }
        return p;
      })
    );

    checkStockLevels();
  };

  const adjustStock = (productId: string, quantity: number, reason: string, changedBy: string) => {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    // Add adjustment transaction
    addStockTransaction({
      product_id: productId,
      transaction_type: 'adjustment',
      quantity,
      unit_price: product.hpp,
      total_amount: quantity * product.hpp,
      reference_number: `ADJ-${Date.now()}`,
      notes: reason,
      created_by: changedBy,
    });

    // Create audit log
    const newLog: AuditLog = {
      id: `log-${Date.now()}`,
      entity_type: 'stock',
      entity_id: productId,
      action: 'adjust',
      field_changed: 'current_stock',
      old_value: String(product.current_stock),
      new_value: String(product.current_stock + quantity),
      reason,
      changed_by: changedBy,
      changed_at: new Date().toISOString(),
    };
    setAuditLogs(prev => [...prev, newLog]);
  };

  const updatePrice = (productId: string, newPrice: number, reason: string, changedBy: string) => {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    // Calculate new margin based on HPP
    const newMargin = ((newPrice - product.hpp) / product.hpp) * 100;

    updateProduct(
      productId,
      {
        selling_price: newPrice,
        margin_percentage: Math.round(newMargin * 100) / 100,
      },
      reason,
      changedBy
    );

    // Create specific audit log for price change
    const newLog: AuditLog = {
      id: `log-${Date.now()}`,
      entity_type: 'price',
      entity_id: productId,
      action: 'update',
      field_changed: 'selling_price',
      old_value: String(product.selling_price),
      new_value: String(newPrice),
      reason,
      changed_by: changedBy,
      changed_at: new Date().toISOString(),
    };
    setAuditLogs(prev => [...prev, newLog]);
  };

  const checkStockLevels = () => {
    products.forEach(product => {
      // Check min stock
      if (product.current_stock < product.min_stock) {
        const existingNotif = stockNotifications.find(
          n => n.product_id === product.id && n.notification_type === 'min_stock' && !n.is_read
        );

        if (!existingNotif) {
          const notification: StockNotification = {
            id: `notif-${Date.now()}-${product.id}`,
            product_id: product.id,
            notification_type: 'min_stock',
            message: `${product.name} is below minimum stock level (${product.current_stock}/${product.min_stock})`,
            severity: 'high',
            is_read: false,
            created_at: new Date().toISOString(),
          };
          setStockNotifications(prev => [...prev, notification]);
        }
      }

      // Check max stock
      if (product.current_stock > product.max_stock * 0.9) {
        const existingNotif = stockNotifications.find(
          n => n.product_id === product.id && n.notification_type === 'max_stock' && !n.is_read
        );

        if (!existingNotif) {
          const notification: StockNotification = {
            id: `notif-${Date.now()}-${product.id}`,
            product_id: product.id,
            notification_type: 'max_stock',
            message: `${product.name} is approaching maximum stock level (${product.current_stock}/${product.max_stock})`,
            severity: 'low',
            is_read: false,
            created_at: new Date().toISOString(),
          };
          setStockNotifications(prev => [...prev, notification]);
        }
      }
    });
  };

  const markNotificationRead = (notificationId: string) => {
    setStockNotifications(prev =>
      prev.map(n => (n.id === notificationId ? { ...n, is_read: true } : n))
    );
  };

  const addSale = (sale: Omit<Sale, 'id' | 'created_at'>) => {
    const newSale: Sale = {
      ...sale,
      id: `sale-${Date.now()}`,
      created_at: new Date().toISOString(),
    };

    setSales(prev => [...prev, newSale]);

    // Create stock transactions for each item
    sale.items.forEach(item => {
      addStockTransaction({
        product_id: item.product_id,
        transaction_type: 'sale',
        quantity: -item.quantity, // Negative for stock reduction
        unit_price: item.unit_price,
        total_amount: item.total,
        reference_number: sale.sale_number,
        notes: `Sale to ${sale.customer_name || 'Customer'}`,
        created_by: sale.created_by,
      });
    });
  };

  const addSupplier = (supplier: Omit<Supplier, 'id' | 'created_at'>) => {
    const newSupplier: Supplier = {
      ...supplier,
      id: `sup-${Date.now()}`,
      created_at: new Date().toISOString(),
    };
    setSuppliers(prev => [...prev, newSupplier]);
  };

  const updateSupplier = (id: string, updates: Partial<Supplier>) => {
    setSuppliers(prev => prev.map(s => (s.id === id ? { ...s, ...updates } : s)));
  };

  const addCategory = (category: Omit<Category, 'id' | 'created_at'>) => {
    const newCategory: Category = {
      ...category,
      id: `cat-${Date.now()}`,
      created_at: new Date().toISOString(),
    };
    setSuppliers(prev => [...prev, newCategory as any]); // This would need a separate categories state
  };

  const addEmployee = (employee: Omit<Employee, 'id' | 'created_at'>) => {
    const newEmployee: Employee = {
      ...employee,
      id: `emp-${Date.now()}`,
      created_at: new Date().toISOString(),
    };
    setEmployees(prev => [...prev, newEmployee]);
  };

  const updateEmployee = (id: string, updates: Partial<Employee>) => {
    setEmployees(prev => prev.map(e => (e.id === id ? { ...e, ...updates } : e)));
  };

  const deleteEmployee = (id: string) => {
    setEmployees(prev => prev.filter(e => e.id !== id));
  };

  const addAttendance = (attendance: Omit<Attendance, 'id' | 'created_at'>) => {
    const newAttendance: Attendance = {
      ...attendance,
      id: `att-${Date.now()}`,
      created_at: new Date().toISOString(),
    };
    setAttendances(prev => [...prev, newAttendance]);
  };

  const updateAttendance = (id: string, updates: Partial<Attendance>) => {
    setAttendances(prev => prev.map(a => (a.id === id ? { ...a, ...updates } : a)));
  };

  const deleteAttendance = (id: string) => {
    setAttendances(prev => prev.filter(a => a.id !== id));
  };

  const addPayroll = (payroll: Omit<Payroll, 'id' | 'created_at'>) => {
    const newPayroll: Payroll = {
      ...payroll,
      id: `pay-${Date.now()}`,
      created_at: new Date().toISOString(),
    };
    setPayrolls(prev => [...prev, newPayroll]);
  };

  const updatePayroll = (id: string, updates: Partial<Payroll>) => {
    setPayrolls(prev => prev.map(p => (p.id === id ? { ...p, ...updates } : p)));
  };

  const deletePayroll = (id: string) => {
    setPayrolls(prev => prev.filter(p => p.id !== id));
  };

  // Operational Item operations
  const addOperationalItem = (item: Omit<OperationalItem, 'id' | 'created_at' | 'updated_at'>) => {
    const newItem: OperationalItem = {
      ...item,
      id: `opi-${Date.now()}`,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
    };
    setOperationalItems(prev => [...prev, newItem]);
  };

  const updateOperationalItem = (id: string, updates: Partial<OperationalItem>) => {
    setOperationalItems(prev =>
      prev.map(item =>
        item.id === id
          ? { ...item, ...updates, updated_at: new Date().toISOString() }
          : item
      )
    );
  };

  const deleteOperationalItem = (id: string) => {
    setOperationalItems(prev => prev.filter(item => item.id !== id));
  };

  // Operational Expense operations
  const addOperationalExpense = (expense: Omit<OperationalExpense, 'id' | 'created_at'>) => {
    const newExpense: OperationalExpense = {
      ...expense,
      id: `opx-${Date.now()}`,
      created_at: new Date().toISOString(),
    };
    setOperationalExpenses(prev => [...prev, newExpense]);

    // Update operational item stock based on transaction type
    if (expense.transaction_type === 'purchase') {
      // Increase stock
      setOperationalItems(prev =>
        prev.map(item =>
          item.id === expense.item_id
            ? {
                ...item,
                current_stock: item.current_stock + expense.quantity,
                last_purchase_date: expense.date,
                last_purchase_quantity: expense.quantity,
                updated_at: new Date().toISOString(),
              }
            : item
        )
      );
    } else if (expense.transaction_type === 'usage') {
      // Decrease stock
      setOperationalItems(prev =>
        prev.map(item =>
          item.id === expense.item_id
            ? {
                ...item,
                current_stock: item.current_stock - expense.quantity,
                updated_at: new Date().toISOString(),
              }
            : item
        )
      );
    }
  };

  const deleteOperationalExpense = (id: string) => {
    setOperationalExpenses(prev => prev.filter(exp => exp.id !== id));
  };

  // Customer operations
  const addCustomer = (customer: Omit<Customer, 'id' | 'created_at'>) => {
    const newCustomer: Customer = {
      ...customer,
      id: `cust-${Date.now()}`,
      created_at: new Date().toISOString(),
    };
    setCustomers(prev => [...prev, newCustomer]);
  };

  const updateCustomer = (id: string, updates: Partial<Customer>) => {
    setCustomers(prev => prev.map(c => (c.id === id ? { ...c, ...updates } : c)));
  };

  const deleteCustomer = (id: string) => {
    setCustomers(prev => prev.filter(c => c.id !== id));
  };

  // Customer Transaction operations
  const addCustomerTransaction = (transaction: Omit<CustomerTransaction, 'id' | 'created_at' | 'updated_at'>) => {
    const newTransaction: CustomerTransaction = {
      ...transaction,
      id: `ctx-${Date.now()}`,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
    };
    setCustomerTransactions(prev => [...prev, newTransaction]);

    // Update customer's total outstanding
    setCustomers(prev =>
      prev.map(c =>
        c.id === transaction.customer_id
          ? { ...c, total_outstanding: c.total_outstanding + transaction.remaining_balance }
          : c
      )
    );
  };

  const updateCustomerTransaction = (id: string, updates: Partial<CustomerTransaction>) => {
    setCustomerTransactions(prev =>
      prev.map(t =>
        t.id === id ? { ...t, ...updates, updated_at: new Date().toISOString() } : t
      )
    );
  };

  const addCustomerPayment = (transactionId: string, payment: Omit<CustomerPayment, 'id' | 'created_at'>) => {
    const newPayment: CustomerPayment = {
      ...payment,
      id: `cpay-${Date.now()}`,
      created_at: new Date().toISOString(),
    };

    setCustomerTransactions(prev =>
      prev.map(t => {
        if (t.id === transactionId) {
          const updatedPayments = [...t.payments, newPayment];
          const newTotalPaid = t.total_paid + payment.total_amount;
          const newRemainingBalance = t.total_amount - newTotalPaid;
          const newPaymentStatus: 'unpaid' | 'partial' | 'paid' =
            newRemainingBalance === 0 ? 'paid' : newRemainingBalance < t.total_amount ? 'partial' : 'unpaid';

          // Update customer's total outstanding
          setCustomers(prev2 =>
            prev2.map(c =>
              c.id === t.customer_id
                ? { ...c, total_outstanding: c.total_outstanding - payment.total_amount }
                : c
            )
          );

          return {
            ...t,
            payments: updatedPayments,
            total_paid: newTotalPaid,
            remaining_balance: newRemainingBalance,
            payment_status: newPaymentStatus,
            updated_at: new Date().toISOString(),
          };
        }
        return t;
      })
    );
  };

  return (
    <InventoryContext.Provider
      value={{
        products,
        categories,
        suppliers,
        stockTransactions,
        auditLogs,
        stockNotifications,
        sales,
        employees,
        attendances,
        payrolls,
        addProduct,
        updateProduct,
        deleteProduct,
        addStockTransaction,
        adjustStock,
        updatePrice,
        markNotificationRead,
        checkStockLevels,
        addSale,
        addSupplier,
        updateSupplier,
        addCategory,
        addEmployee,
        updateEmployee,
        deleteEmployee,
        addAttendance,
        updateAttendance,
        deleteAttendance,
        addPayroll,
        updatePayroll,
        deletePayroll,
        operationalItems,
        operationalExpenses,
        addOperationalItem,
        updateOperationalItem,
        deleteOperationalItem,
        addOperationalExpense,
        deleteOperationalExpense,
        customers,
        customerTransactions,
        addCustomer,
        updateCustomer,
        deleteCustomer,
        addCustomerTransaction,
        updateCustomerTransaction,
        addCustomerPayment,
      }}
    >
      {children}
    </InventoryContext.Provider>
  );
};