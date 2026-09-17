import React, { useEffect, useState } from 'react'
export default function TaskForm({onSave,editing,onUpdate,onCancel}){
  const [title,setTitle]=useState('')
  const [description,setDescription]=useState('')
  const [priority,setPriority]=useState(0)
  const [dueDate,setDueDate]=useState('')
  const [status,setStatus]=useState('todo')
  const [error,setError]=useState(null)
  useEffect(()=>{
    if(editing){ setTitle(editing.title||''); setDescription(editing.description||''); setPriority(editing.priority||0); setDueDate(editing.due_date||''); setStatus(editing.status||'todo') } else { setTitle(''); setDescription(''); setPriority(0); setDueDate(''); setStatus('todo') }
  },[editing])
  async function submit(e){ e.preventDefault(); setError(null); if(!title.trim()){ setError('Title required'); return } const payload={title,description,status,priority:Number(priority),due_date:dueDate||null}; try{ if(editing) await onUpdate(editing.id,payload); else await onSave(payload); }catch(err){ setError(err.data?.error||'Save failed') } }
  return (
    <form className="taskform" onSubmit={submit}>
      <input placeholder="Title" value={title} onChange={e=>setTitle(e.target.value)} />
      <input placeholder="Description" value={description} onChange={e=>setDescription(e.target.value)} />
      <select value={status} onChange={e=>setStatus(e.target.value)}>
        <option value="todo">todo</option>
        <option value="in-progress">in-progress</option>
        <option value="done">done</option>
      </select>
      <input placeholder="Priority" type="number" value={priority} onChange={e=>setPriority(e.target.value)} />
      <input placeholder="Due date" type="date" value={dueDate} onChange={e=>setDueDate(e.target.value)} />
      <div>
        <button type="submit">{editing? 'Update' : 'Add'}</button>
        {editing && <button type="button" onClick={onCancel}>Cancel</button>}
      </div>
      {error && <div className="error">{error}</div>}
    </form>
  )
}
