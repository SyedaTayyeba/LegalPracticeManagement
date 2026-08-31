// import { api } from '../lib/axios'

// export const taskApi = {
//   list: (params) => api.get('/firm/tasks', { params }).then((r) => r.data),

//   show: (taskId) => api.get(`/firm/tasks/${taskId}`).then((r) => r.data),

//   create: (payload) => api.post('/firm/tasks', payload).then((r) => r.data),

//   update: (taskId, payload) => api.patch(`/firm/tasks/${taskId}`, payload).then((r) => r.data),

//   destroy: (taskId) => api.delete(`/firm/tasks/${taskId}`).then((r) => r.data),
// }
import { api } from "../lib/axios";
import { demoTaskApi } from "../demo/demoApi";

const DEMO_MODE = import.meta.env.VITE_DEMO_MODE === "true";

export const taskApi = {
  list: (params) =>
    DEMO_MODE
      ? demoTaskApi.list(params)
      : api.get("/firm/tasks", { params }).then((r) => r.data),

  show: (taskId) =>
    DEMO_MODE
      ? demoTaskApi.show(taskId)
      : api.get(`/firm/tasks/${taskId}`).then((r) => r.data),

  create: (payload) =>
    DEMO_MODE
      ? demoTaskApi.create(payload)
      : api.post("/firm/tasks", payload).then((r) => r.data),

  update: (taskId, payload) =>
    DEMO_MODE
      ? demoTaskApi.update(taskId, payload)
      : api.patch(`/firm/tasks/${taskId}`, payload).then((r) => r.data),

  destroy: (taskId) =>
    DEMO_MODE
      ? demoTaskApi.destroy(taskId)
      : api.delete(`/firm/tasks/${taskId}`).then((r) => r.data),
};
