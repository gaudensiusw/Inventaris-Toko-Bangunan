import React, { useState, useMemo } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { useInventory } from '../contexts/InventoryContext';
import { Card } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '../components/ui/dialog';
import {
  User,
  Calendar,
  DollarSign,
  Clock,
  CheckCircle,
  XCircle,
  FileText,
  Plus,
  Edit,
  ChevronLeft,
  ChevronRight,
  Gift,
  TrendingUp,
} from 'lucide-react';
import { cn } from '../components/ui/utils';
import { Employee, Attendance, Payroll } from '../data/mockData';

export const EmployeesPage: React.FC = () => {
  const { user } = useAuth();
  const { employees, attendances, payrolls } = useInventory();
  const [selectedMonth, setSelectedMonth] = useState(new Date());
  const [selectedEmployee, setSelectedEmployee] = useState<Employee | null>(null);
  const [showEmployeeDialog, setShowEmployeeDialog] = useState(false);
  const [showAttendanceDialog, setShowAttendanceDialog] = useState(false);
  const [showPayrollDialog, setShowPayrollDialog] = useState(false);
  const [selectedDate, setSelectedDate] = useState<string>('');
  
  // Form states
  const [employeeForm, setEmployeeForm] = useState<Partial<Employee>>({
    employee_code: '',
    name: '',
    position: '',
    phone: '',
    email: '',
    address: '',
    join_date: new Date().toISOString(),
    status: 'active',
    daily_salary: 100000,
    bonus: 0,
  });

  const [attendanceForm, setAttendanceForm] = useState<Partial<Attendance>>({
    status: 'present',
    check_in: '08:00',
    check_out: '17:00',
    notes: '',
  });

  // Get attendance data for selected month
  const monthlyAttendances = useMemo(() => {
    if (!selectedEmployee) return [];
    
    const startOfMonth = new Date(selectedMonth.getFullYear(), selectedMonth.getMonth(), 1);
    const endOfMonth = new Date(selectedMonth.getFullYear(), selectedMonth.getMonth() + 1, 0);
    
    return attendances.filter(att => {
      const attDate = new Date(att.date);
      return att.employee_id === selectedEmployee.id &&
             attDate >= startOfMonth &&
             attDate <= endOfMonth;
    });
  }, [selectedEmployee, selectedMonth]);

  // Calculate monthly summary for selected employee
  const monthlySummary = useMemo(() => {
    if (!selectedEmployee) return null;
    
    const present = monthlyAttendances.filter(a => a.status === 'present').length;
    const absent = monthlyAttendances.filter(a => a.status === 'absent').length;
    const sick = monthlyAttendances.filter(a => a.status === 'sick').length;
    const leave = monthlyAttendances.filter(a => a.status === 'leave').length;
    const baseSalary = present * selectedEmployee.daily_salary;
    const totalSalary = baseSalary + selectedEmployee.bonus;
    
    return {
      present,
      absent,
      sick,
      leave,
      baseSalary,
      totalSalary,
    };
  }, [selectedEmployee, monthlyAttendances]);

  // Generate calendar days
  const calendarDays = useMemo(() => {
    const year = selectedMonth.getFullYear();
    const month = selectedMonth.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());

    const days = [];
    const endDate = new Date(lastDay);
    endDate.setDate(endDate.getDate() + (6 - lastDay.getDay()));

    for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
      days.push(new Date(d));
    }

    return days;
  }, [selectedMonth]);

  const handlePreviousMonth = () => {
    setSelectedMonth(new Date(selectedMonth.getFullYear(), selectedMonth.getMonth() - 1, 1));
  };

  const handleNextMonth = () => {
    setSelectedMonth(new Date(selectedMonth.getFullYear(), selectedMonth.getMonth() + 1, 1));
  };

  const getAttendanceForDate = (date: Date) => {
    const dateStr = date.toISOString().split('T')[0];
    return monthlyAttendances.find(att => att.date === dateStr);
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'present':
        return 'bg-green-100 text-green-700 border-green-300';
      case 'absent':
        return 'bg-red-100 text-red-700 border-red-300';
      case 'sick':
        return 'bg-yellow-100 text-yellow-700 border-yellow-300';
      case 'leave':
        return 'bg-blue-100 text-blue-700 border-blue-300';
      case 'holiday':
        return 'bg-gray-100 text-gray-700 border-gray-300';
      default:
        return '';
    }
  };

  const getStatusBadge = (status: string) => {
    const labels: { [key: string]: string } = {
      present: 'Hadir',
      absent: 'Tidak Hadir',
      sick: 'Sakit',
      leave: 'Cuti',
      holiday: 'Libur',
    };
    return labels[status] || status;
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

  const handleAddEmployee = () => {
    setEmployeeForm({
      employee_code: '',
      name: '',
      position: '',
      phone: '',
      email: '',
      address: '',
      join_date: new Date().toISOString(),
      status: 'active',
      daily_salary: 100000,
      bonus: 0,
    });
    setShowEmployeeDialog(true);
  };

  const handleEditEmployee = (employee: Employee) => {
    setEmployeeForm(employee);
    setShowEmployeeDialog(true);
  };

  const handleMarkAttendance = (date: Date) => {
    if (!selectedEmployee) return;
    
    const dateStr = date.toISOString().split('T')[0];
    const existing = getAttendanceForDate(date);
    
    setSelectedDate(dateStr);
    setAttendanceForm({
      status: existing?.status || 'present',
      check_in: existing?.check_in || '08:00',
      check_out: existing?.check_out || '17:00',
      notes: existing?.notes || '',
    });
    setShowAttendanceDialog(true);
  };

  const handleGeneratePayroll = () => {
    if (!selectedEmployee) return;
    setShowPayrollDialog(true);
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">Manajemen Karyawan</h1>
          <p className="text-slate-600 mt-1">
            Kelola data karyawan, absensi, dan penggajian
          </p>
        </div>
        <Button onClick={handleAddEmployee}>
          <Plus className="w-4 h-4 mr-2" />
          Tambah Karyawan
        </Button>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Employee List */}
        <Card className="p-6 lg:col-span-1">
          <h2 className="text-lg font-bold text-slate-900 mb-4">Daftar Karyawan</h2>
          <div className="space-y-2 max-h-[600px] overflow-y-auto">
            {employees.map((employee) => (
              <div
                key={employee.id}
                onClick={() => setSelectedEmployee(employee)}
                className={cn(
                  'p-4 rounded-lg border-2 cursor-pointer transition-all',
                  selectedEmployee?.id === employee.id
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-slate-200 hover:border-slate-300 bg-white'
                )}
              >
                <div className="flex items-start justify-between mb-2">
                  <div className="flex-1">
                    <p className="font-semibold text-slate-900">{employee.name}</p>
                    <p className="text-sm text-slate-600">{employee.position}</p>
                    <p className="text-xs text-slate-500 mt-1">{employee.employee_code}</p>
                  </div>
                  <Badge
                    className={cn(
                      employee.status === 'active'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-gray-100 text-gray-700'
                    )}
                  >
                    {employee.status === 'active' ? 'Aktif' : 'Non-Aktif'}
                  </Badge>
                </div>
                <div className="flex items-center justify-between text-sm mt-3 pt-3 border-t">
                  <div>
                    <p className="text-xs text-slate-500">Gaji/Hari</p>
                    <p className="font-semibold text-slate-900">{formatCurrency(employee.daily_salary)}</p>
                  </div>
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={(e) => {
                      e.stopPropagation();
                      handleEditEmployee(employee);
                    }}
                  >
                    <Edit className="w-3 h-3" />
                  </Button>
                </div>
              </div>
            ))}
          </div>
        </Card>

        {/* Calendar & Details */}
        <Card className="p-6 lg:col-span-2">
          {selectedEmployee ? (
            <>
              {/* Employee Info */}
              <div className="flex items-center justify-between mb-6 pb-4 border-b">
                <div className="flex items-center gap-4">
                  <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                    <User className="w-8 h-8 text-blue-600" />
                  </div>
                  <div>
                    <h2 className="text-xl font-bold text-slate-900">{selectedEmployee.name}</h2>
                    <p className="text-slate-600">{selectedEmployee.position}</p>
                    <p className="text-sm text-slate-500">Bergabung: {formatDate(selectedEmployee.join_date)}</p>
                  </div>
                </div>
                <div className="text-right">
                  <p className="text-sm text-slate-500">Gaji Pokok/Hari</p>
                  <p className="text-2xl font-bold text-slate-900">{formatCurrency(selectedEmployee.daily_salary)}</p>
                  <p className="text-sm text-slate-500 mt-1">Bonus: {formatCurrency(selectedEmployee.bonus)}</p>
                </div>
              </div>

              {/* Monthly Summary */}
              {monthlySummary && (
                <div className="grid grid-cols-4 gap-3 mb-6">
                  <div className="bg-green-50 p-3 rounded-lg border border-green-200">
                    <p className="text-xs text-green-700 mb-1">Hadir</p>
                    <p className="text-2xl font-bold text-green-900">{monthlySummary.present}</p>
                  </div>
                  <div className="bg-red-50 p-3 rounded-lg border border-red-200">
                    <p className="text-xs text-red-700 mb-1">Tidak Hadir</p>
                    <p className="text-2xl font-bold text-red-900">{monthlySummary.absent}</p>
                  </div>
                  <div className="bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                    <p className="text-xs text-yellow-700 mb-1">Sakit</p>
                    <p className="text-2xl font-bold text-yellow-900">{monthlySummary.sick}</p>
                  </div>
                  <div className="bg-blue-50 p-3 rounded-lg border border-blue-200">
                    <p className="text-xs text-blue-700 mb-1">Cuti</p>
                    <p className="text-2xl font-bold text-blue-900">{monthlySummary.leave}</p>
                  </div>
                </div>
              )}

              {/* Salary Summary */}
              {monthlySummary && (
                <div className="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-200 mb-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm text-slate-600 mb-2">Estimasi Gaji Bulan Ini</p>
                      <div className="space-y-1">
                        <div className="flex items-center gap-2 text-sm">
                          <span className="text-slate-600">Gaji Pokok:</span>
                          <span className="font-semibold">{monthlySummary.present} hari × {formatCurrency(selectedEmployee.daily_salary)} = {formatCurrency(monthlySummary.baseSalary)}</span>
                        </div>
                        <div className="flex items-center gap-2 text-sm">
                          <span className="text-slate-600">Bonus:</span>
                          <span className="font-semibold">{formatCurrency(selectedEmployee.bonus)}</span>
                        </div>
                      </div>
                    </div>
                    <div className="text-right">
                      <p className="text-sm text-slate-600 mb-1">Total</p>
                      <p className="text-3xl font-bold text-blue-900">{formatCurrency(monthlySummary.totalSalary)}</p>
                    </div>
                  </div>
                  <div className="mt-4 pt-4 border-t border-blue-200">
                    <Button onClick={handleGeneratePayroll} className="w-full">
                      <FileText className="w-4 h-4 mr-2" />
                      Generate Slip Gaji
                    </Button>
                  </div>
                </div>
              )}

              {/* Calendar Navigation */}
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-bold text-slate-900">Kalender Absensi</h3>
                <div className="flex items-center gap-2">
                  <Button variant="outline" size="sm" onClick={handlePreviousMonth}>
                    <ChevronLeft className="w-4 h-4" />
                  </Button>
                  <div className="text-center min-w-[150px]">
                    <p className="font-semibold">
                      {selectedMonth.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}
                    </p>
                  </div>
                  <Button variant="outline" size="sm" onClick={handleNextMonth}>
                    <ChevronRight className="w-4 h-4" />
                  </Button>
                </div>
              </div>

              {/* Calendar Grid */}
              <div className="grid grid-cols-7 gap-2">
                {/* Day headers */}
                {['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'].map(day => (
                  <div key={day} className="text-center text-xs font-semibold text-slate-600 py-2">
                    {day}
                  </div>
                ))}

                {/* Calendar days */}
                {calendarDays.map((day, index) => {
                  const attendance = getAttendanceForDate(day);
                  const isCurrentMonth = day.getMonth() === selectedMonth.getMonth();
                  const isToday = day.toDateString() === new Date().toDateString();
                  const isPast = day < new Date(new Date().setHours(0, 0, 0, 0));

                  return (
                    <div
                      key={index}
                      onClick={() => isCurrentMonth && handleMarkAttendance(day)}
                      className={cn(
                        'min-h-[70px] p-2 border rounded-lg cursor-pointer transition-all',
                        !isCurrentMonth && 'bg-slate-50 text-slate-400 cursor-not-allowed',
                        isCurrentMonth && 'bg-white hover:border-blue-300',
                        isToday && 'border-blue-500 border-2',
                        attendance && getStatusColor(attendance.status)
                      )}
                    >
                      <div className="text-sm font-semibold mb-1">{day.getDate()}</div>
                      {attendance && (
                        <div className="space-y-1">
                          <div className="text-xs font-medium">
                            {getStatusBadge(attendance.status)}
                          </div>
                          {attendance.check_in && (
                            <div className="text-xs text-slate-600">
                              {attendance.check_in} - {attendance.check_out}
                            </div>
                          )}
                        </div>
                      )}
                      {!attendance && isCurrentMonth && isPast && (
                        <div className="text-xs text-red-500 font-medium">Belum diisi</div>
                      )}
                    </div>
                  );
                })}
              </div>

              {/* Legend */}
              <div className="flex flex-wrap items-center gap-4 mt-4 pt-4 border-t">
                <div className="flex items-center gap-2">
                  <div className="w-4 h-4 bg-green-100 border border-green-300 rounded"></div>
                  <span className="text-sm text-slate-600">Hadir</span>
                </div>
                <div className="flex items-center gap-2">
                  <div className="w-4 h-4 bg-red-100 border border-red-300 rounded"></div>
                  <span className="text-sm text-slate-600">Tidak Hadir</span>
                </div>
                <div className="flex items-center gap-2">
                  <div className="w-4 h-4 bg-yellow-100 border border-yellow-300 rounded"></div>
                  <span className="text-sm text-slate-600">Sakit</span>
                </div>
                <div className="flex items-center gap-2">
                  <div className="w-4 h-4 bg-blue-100 border border-blue-300 rounded"></div>
                  <span className="text-sm text-slate-600">Cuti</span>
                </div>
                <div className="flex items-center gap-2">
                  <div className="w-4 h-4 bg-gray-100 border border-gray-300 rounded"></div>
                  <span className="text-sm text-slate-600">Libur</span>
                </div>
              </div>
            </>
          ) : (
            <div className="flex flex-col items-center justify-center h-full py-12">
              <User className="w-16 h-16 text-slate-300 mb-4" />
              <p className="text-slate-600 text-lg">Pilih karyawan untuk melihat detail</p>
            </div>
          )}
        </Card>
      </div>

      {/* Employee Dialog */}
      <Dialog open={showEmployeeDialog} onOpenChange={setShowEmployeeDialog}>
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>
              {employeeForm.id ? 'Edit Karyawan' : 'Tambah Karyawan Baru'}
            </DialogTitle>
            <DialogDescription>
              {employeeForm.id ? 'Update informasi karyawan' : 'Tambahkan karyawan baru ke sistem'}
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4 mt-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label>Kode Karyawan *</Label>
                <Input
                  value={employeeForm.employee_code}
                  onChange={(e) => setEmployeeForm({ ...employeeForm, employee_code: e.target.value })}
                  placeholder="EMP-001"
                />
              </div>
              <div>
                <Label>Status *</Label>
                <select
                  value={employeeForm.status}
                  onChange={(e) => setEmployeeForm({ ...employeeForm, status: e.target.value as 'active' | 'inactive' })}
                  className="w-full px-3 py-2 border rounded-lg"
                >
                  <option value="active">Aktif</option>
                  <option value="inactive">Non-Aktif</option>
                </select>
              </div>
            </div>

            <div>
              <Label>Nama Lengkap *</Label>
              <Input
                value={employeeForm.name}
                onChange={(e) => setEmployeeForm({ ...employeeForm, name: e.target.value })}
                placeholder="Nama karyawan"
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label>Posisi *</Label>
                <Input
                  value={employeeForm.position}
                  onChange={(e) => setEmployeeForm({ ...employeeForm, position: e.target.value })}
                  placeholder="Kasir, Staff Gudang, dll"
                />
              </div>
              <div>
                <Label>Tanggal Bergabung *</Label>
                <Input
                  type="date"
                  value={employeeForm.join_date ? new Date(employeeForm.join_date).toISOString().split('T')[0] : ''}
                  onChange={(e) => setEmployeeForm({ ...employeeForm, join_date: new Date(e.target.value).toISOString() })}
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label>Telepon *</Label>
                <Input
                  value={employeeForm.phone}
                  onChange={(e) => setEmployeeForm({ ...employeeForm, phone: e.target.value })}
                  placeholder="+62-812-3456-7890"
                />
              </div>
              <div>
                <Label>Email</Label>
                <Input
                  type="email"
                  value={employeeForm.email}
                  onChange={(e) => setEmployeeForm({ ...employeeForm, email: e.target.value })}
                  placeholder="email@example.com"
                />
              </div>
            </div>

            <div>
              <Label>Alamat</Label>
              <Input
                value={employeeForm.address}
                onChange={(e) => setEmployeeForm({ ...employeeForm, address: e.target.value })}
                placeholder="Alamat lengkap"
              />
            </div>

            <div className="border-t pt-4">
              <h4 className="font-medium mb-3">Informasi Gaji</h4>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label>Gaji Pokok per Hari *</Label>
                  <Input
                    type="number"
                    value={employeeForm.daily_salary}
                    onChange={(e) => setEmployeeForm({ ...employeeForm, daily_salary: Number(e.target.value) })}
                    placeholder="100000"
                  />
                  <p className="text-xs text-slate-500 mt-1">
                    Gaji yang diterima untuk setiap hari hadir
                  </p>
                </div>
                <div>
                  <Label>Bonus Tetap</Label>
                  <Input
                    type="number"
                    value={employeeForm.bonus}
                    onChange={(e) => setEmployeeForm({ ...employeeForm, bonus: Number(e.target.value) })}
                    placeholder="500000"
                  />
                  <p className="text-xs text-slate-500 mt-1">
                    Bonus yang diberikan setiap bulan
                  </p>
                </div>
              </div>
            </div>

            <div className="flex gap-2 pt-4">
              <Button onClick={() => setShowEmployeeDialog(false)} variant="outline" className="flex-1">
                Batal
              </Button>
              <Button onClick={() => setShowEmployeeDialog(false)} className="flex-1">
                {employeeForm.id ? 'Update' : 'Simpan'}
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Attendance Dialog */}
      <Dialog open={showAttendanceDialog} onOpenChange={setShowAttendanceDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Input Absensi - {formatDate(selectedDate)}</DialogTitle>
            <DialogDescription>
              Catat kehadiran karyawan untuk tanggal yang dipilih
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4 mt-4">
            <div>
              <Label>Status Kehadiran *</Label>
              <select
                value={attendanceForm.status}
                onChange={(e) => setAttendanceForm({ ...attendanceForm, status: e.target.value as any })}
                className="w-full px-3 py-2 border rounded-lg"
              >
                <option value="present">Hadir</option>
                <option value="absent">Tidak Hadir</option>
                <option value="sick">Sakit</option>
                <option value="leave">Cuti</option>
                <option value="holiday">Libur</option>
              </select>
            </div>

            {attendanceForm.status === 'present' && (
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label>Jam Masuk</Label>
                  <Input
                    type="time"
                    value={attendanceForm.check_in}
                    onChange={(e) => setAttendanceForm({ ...attendanceForm, check_in: e.target.value })}
                  />
                </div>
                <div>
                  <Label>Jam Keluar</Label>
                  <Input
                    type="time"
                    value={attendanceForm.check_out}
                    onChange={(e) => setAttendanceForm({ ...attendanceForm, check_out: e.target.value })}
                  />
                </div>
              </div>
            )}

            <div>
              <Label>Catatan</Label>
              <Input
                value={attendanceForm.notes}
                onChange={(e) => setAttendanceForm({ ...attendanceForm, notes: e.target.value })}
                placeholder="Catatan tambahan (opsional)"
              />
            </div>

            <div className="flex gap-2 pt-4">
              <Button onClick={() => setShowAttendanceDialog(false)} variant="outline" className="flex-1">
                Batal
              </Button>
              <Button onClick={() => setShowAttendanceDialog(false)} className="flex-1">
                Simpan
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Payroll Dialog */}
      <Dialog open={showPayrollDialog} onOpenChange={setShowPayrollDialog}>
        <DialogContent className="max-w-3xl">
          <DialogHeader>
            <DialogTitle>Slip Gaji - {selectedEmployee?.name}</DialogTitle>
            <DialogDescription>
              Ringkasan penggajian bulanan berdasarkan kehadiran
            </DialogDescription>
          </DialogHeader>
          {selectedEmployee && monthlySummary && (
            <div className="space-y-4 mt-4">
              {/* Payroll Header */}
              <div className="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-6 rounded-lg">
                <div className="flex items-center justify-between">
                  <div>
                    <h3 className="text-2xl font-bold">{selectedEmployee.name}</h3>
                    <p className="text-blue-100">{selectedEmployee.position} - {selectedEmployee.employee_code}</p>
                  </div>
                  <div className="text-right">
                    <p className="text-sm text-blue-100">Periode</p>
                    <p className="text-xl font-bold">
                      {selectedMonth.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}
                    </p>
                  </div>
                </div>
              </div>

              {/* Attendance Summary */}
              <div className="bg-slate-50 p-4 rounded-lg">
                <h4 className="font-semibold text-slate-900 mb-3">Ringkasan Kehadiran</h4>
                <div className="grid grid-cols-4 gap-3">
                  <div className="text-center">
                    <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                      <CheckCircle className="w-6 h-6 text-green-600" />
                    </div>
                    <p className="text-2xl font-bold text-green-900">{monthlySummary.present}</p>
                    <p className="text-xs text-slate-600">Hadir</p>
                  </div>
                  <div className="text-center">
                    <div className="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-2">
                      <XCircle className="w-6 h-6 text-red-600" />
                    </div>
                    <p className="text-2xl font-bold text-red-900">{monthlySummary.absent}</p>
                    <p className="text-xs text-slate-600">Tidak Hadir</p>
                  </div>
                  <div className="text-center">
                    <div className="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-2">
                      <Clock className="w-6 h-6 text-yellow-600" />
                    </div>
                    <p className="text-2xl font-bold text-yellow-900">{monthlySummary.sick}</p>
                    <p className="text-xs text-slate-600">Sakit</p>
                  </div>
                  <div className="text-center">
                    <div className="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                      <Calendar className="w-6 h-6 text-blue-600" />
                    </div>
                    <p className="text-2xl font-bold text-blue-900">{monthlySummary.leave}</p>
                    <p className="text-xs text-slate-600">Cuti</p>
                  </div>
                </div>
              </div>

              {/* Salary Breakdown */}
              <div className="bg-white border rounded-lg p-4">
                <h4 className="font-semibold text-slate-900 mb-3">Rincian Gaji</h4>
                <div className="space-y-3">
                  <div className="flex items-center justify-between py-2 border-b">
                    <div className="flex items-center gap-2">
                      <DollarSign className="w-4 h-4 text-slate-500" />
                      <span className="text-slate-700">Gaji Pokok</span>
                    </div>
                    <span className="font-semibold text-slate-900">
                      {monthlySummary.present} hari × {formatCurrency(selectedEmployee.daily_salary)}
                    </span>
                  </div>
                  <div className="flex items-center justify-between py-2">
                    <span className="text-slate-700 ml-6">Subtotal Gaji Pokok</span>
                    <span className="font-semibold text-slate-900">{formatCurrency(monthlySummary.baseSalary)}</span>
                  </div>
                  <div className="flex items-center justify-between py-2 border-b">
                    <div className="flex items-center gap-2">
                      <Gift className="w-4 h-4 text-slate-500" />
                      <span className="text-slate-700">Bonus</span>
                    </div>
                    <span className="font-semibold text-green-700">{formatCurrency(selectedEmployee.bonus)}</span>
                  </div>
                  <div className="flex items-center justify-between py-3 bg-blue-50 rounded-lg px-4 mt-4">
                    <div className="flex items-center gap-2">
                      <TrendingUp className="w-5 h-5 text-blue-600" />
                      <span className="font-bold text-blue-900">Total Gaji</span>
                    </div>
                    <span className="text-2xl font-bold text-blue-900">{formatCurrency(monthlySummary.totalSalary)}</span>
                  </div>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="flex gap-2 pt-4">
                <Button onClick={() => setShowPayrollDialog(false)} variant="outline" className="flex-1">
                  Tutup
                </Button>
                <Button className="flex-1">
                  <CheckCircle className="w-4 h-4 mr-2" />
                  Proses Pembayaran
                </Button>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
};