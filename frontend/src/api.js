const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://localhost/ali/GOQiiTechnology/taskmanager/public/index.php'
async function request(path, method='GET', body=null, token=null){
  const headers = {'Content-Type':'application/json'}
  if (token) headers['Authorization'] = 'Bearer '+token
  const resp = await fetch(`${API_BASE}${path}`,{method,headers,body: body?JSON.stringify(body):null})
  const text = await resp.text()
  let data = null
  try{ data = text ? JSON.parse(text) : null }catch(e){ data = text }
  if (!resp.ok) throw {status:resp.status, data}
  return data
}
export const api = {
  register: (name,email,password)=>request('/register','POST',{name,email,password}),
  login: (email,password)=>request('/login','POST',{email,password}),
  listTasks: (token, opts = {})=>{
    const qs = new URLSearchParams()
    if (opts.status) qs.set('status', opts.status)
    if (opts.limit) qs.set('limit', String(opts.limit))
    if (opts.offset) qs.set('offset', String(opts.offset))
    const q = qs.toString() ? `?${qs.toString()}` : ''
    return request(`/tasks${q}`,'GET',null,token)
  },
  createTask: (token,body)=>request('/tasks','POST',body,token),
  updateTask: (token,id,body)=>request(`/tasks/${id}`,'PATCH',body,token),
  deleteTask: (token,id)=>request(`/tasks/${id}`,'DELETE',null,token)
}
