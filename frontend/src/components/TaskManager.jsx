import React, { useEffect, useState } from 'react'
import { api } from '../api'
import TaskForm from './TaskForm'
export default function TaskManager({token,onLogout}){
  const [tasks,setTasks]=useState([])
  const [loading,setLoading]=useState(false)
  const [error,setError]=useState(null)
  const [editing,setEditing]=useState(null)
  const [statusFilter,setStatusFilter]=useState('')
  const [limit,setLimit]=useState(10)
  const [offset,setOffset]=useState(0)
  async function load(){
    setLoading(true); setError(null)
    try{ const data = await api.listTasks(token, {status: statusFilter || undefined, limit, offset}); setTasks(data || []) }catch(err){ setError(err.data?.error||'Load failed') }
    setLoading(false)
  }
  useEffect(()=>{ load() },[statusFilter,limit,offset])
  async function create(body){ try{ await api.createTask(token,body); await load() }catch(err){ throw err } }
  async function update(id,body){ try{ await api.updateTask(token,id,body); setEditing(null); await load() }catch(err){ throw err } }
  async function remove(id){ if(!confirm('Delete?')) return; try{ await api.deleteTask(token,id); await load() }catch(err){ setError('Delete failed') } }
  async function changeStatus(id, status){ try{ await api.updateTask(token,id,{status}); await load() }catch(err){ setError('Update failed') } }
  return (
    <div className="app">
      <div className="top">
        <h2>Tasks</h2>
        <div>
          <button onClick={onLogout}>Logout</button>
        </div>
      </div>
      <div className="controls">
        <label>Filter: <select value={statusFilter} onChange={e=>{setOffset(0); setStatusFilter(e.target.value)}}>
          <option value="">All</option>
          <option value="todo">todo</option>
          <option value="in-progress">in-progress</option>
          <option value="done">done</option>
        </select></label>
        <label>Per page: <select value={limit} onChange={e=>{setOffset(0); setLimit(Number(e.target.value))}}>
          <option value={5}>5</option>
          <option value={10}>10</option>
          <option value={25}>25</option>
        </select></label>
      </div>
      <TaskForm onSave={create} editing={editing} onUpdate={update} onCancel={()=>setEditing(null)}/>
      {loading && <div>Loading...</div>}
      {error && <div className="error">{error}</div>}
      <ul className="tasks">
        {tasks.map(t=> (
          <li key={t.id} className="task">
            <div className="left">
              <strong>{t.title}</strong>
              <div>{t.description}</div>
              <div>
                Status: <select value={t.status} onChange={e=>changeStatus(t.id, e.target.value)}>
                  <option value="todo">todo</option>
                  <option value="in-progress">in-progress</option>
                  <option value="done">done</option>
                </select>
                &nbsp; Priority: {t.priority}
              </div>
            </div>
            <div className="actions">
              <button onClick={()=>setEditing(t)}>Edit</button>
              <button onClick={()=>remove(t.id)}>Delete</button>
            </div>
          </li>
        ))}
      </ul>
      <div className="pagination">
        <button disabled={offset===0} onClick={()=>setOffset(Math.max(0, offset - limit))}>Previous</button>
        <button onClick={()=>setOffset(offset + limit)}>Next</button>
      </div>
    </div>
  )
}
