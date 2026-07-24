import { useQuery } from '@tanstack/react-query'
import { reportApi } from '../api/reports'

export function useCaseReport() {
  return useQuery({ queryKey: ['reports', 'cases'], queryFn: () => reportApi.cases().then((d) => d.data) })
}

export function useWorkloadReport() {
  return useQuery({ queryKey: ['reports', 'workload'], queryFn: () => reportApi.workload().then((d) => d.data) })
}

export function useRevenueReport(params = {}) {
  return useQuery({ queryKey: ['reports', 'revenue', params], queryFn: () => reportApi.revenue(params).then((d) => d.data) })
}

export function useBillingStatusReport() {
  return useQuery({ queryKey: ['reports', 'billing-status'], queryFn: () => reportApi.billingStatus().then((d) => d.data) })
}

export function useCasePerformanceReport() {
  return useQuery({ queryKey: ['reports', 'case-performance'], queryFn: () => reportApi.casePerformance().then((d) => d.data) })
}
