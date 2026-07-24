import { createBrowserRouter, Navigate } from 'react-router-dom'
import ProtectedRoute from '../components/ProtectedRoute'
import Login from '../pages/Login'
import RegisterFirm from '../pages/RegisterFirm'
import ForgotPassword from '../pages/ForgotPassword'
import AcceptInvitation from '../pages/AcceptInvitation'
import Dashboard from '../pages/Dashboard'
import Clients from '../pages/Clients'
import ClientDetail from '../pages/ClientDetail'
import Cases from '../pages/Cases'
import CaseDetail from '../pages/CaseDetail'
import Documents from '../pages/Documents'
import Tasks from '../pages/Tasks'
import Calendar from '../pages/Calendar'
import Messages from '../pages/Messages'
import Billing from '../pages/Billing'
import Reports from '../pages/Reports'
import Subscription from '../pages/Subscription'
import ClientPortal from '../pages/ClientPortal'

export const router = createBrowserRouter([
  { path: '/', element: <Navigate to="/login" replace /> },
  { path: '/login', element: <Login /> },
  { path: '/register', element: <RegisterFirm /> },
  { path: '/forgot-password', element: <ForgotPassword /> },
  { path: '/accept-invitation', element: <AcceptInvitation /> },
  {
    element: <ProtectedRoute allowedRoles={['firm_owner', 'lawyer', 'paralegal', 'platform_admin']} />,
    children: [
      { path: '/dashboard', element: <Dashboard /> },
      { path: '/clients', element: <Clients /> },
      { path: '/clients/:clientId', element: <ClientDetail /> },
      { path: '/cases', element: <Cases /> },
      { path: '/cases/:caseId', element: <CaseDetail /> },
      { path: '/documents', element: <Documents /> },
      { path: '/tasks', element: <Tasks /> },
      { path: '/calendar', element: <Calendar /> },
      { path: '/messages', element: <Messages /> },
      { path: '/billing', element: <Billing /> },
      { path: '/reports', element: <Reports /> },
      { path: '/subscription', element: <Subscription /> },
    ],
  },
  {
    element: <ProtectedRoute allowedRoles={['client']} />,
    children: [{ path: '/portal', element: <ClientPortal /> }],
  },
  { path: '*', element: <Navigate to="/login" replace /> },
])
