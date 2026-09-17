import React, { useState } from 'react'
import { api } from '../api'

export default function Login({onLogin}){
  const [email,setEmail]=useState('')
  const [password,setPassword]=useState('')
  const [loading,setLoading]=useState(false)
  const [error,setError]=useState(null)

  async function submit(e){
    e.preventDefault()
    setError(null)
    if(!email||!password){ setError('Enter email and password'); return }
    setLoading(true)
    try{
      const res = await api.login(email,password)
      onLogin(res.token)
    }catch(err){ setError(err.data?.error||'Login failed') }
    setLoading(false)
  }

  return (
    <div className="login center-card">
      <h1 className="title">Task Manager</h1>
      <div className="card">
        <h2 className="card-title">Sign in</h2>
        <form onSubmit={submit} className="login-form">
          <label className="field">
            <div className="label-text">Email</div>
            <input aria-label="email" placeholder="you@example.com" value={email} onChange={e=>setEmail(e.target.value)} />
          </label>
          <label className="field">
            <div className="label-text">Password</div>
            <input aria-label="password" placeholder="Password" type="password" value={password} onChange={e=>setPassword(e.target.value)} />
          </label>
          <button className="primary" disabled={loading}>{loading? 'Loading...' : 'Login'}</button>
        </form>
        {error && <div className="error">{error}</div>}
      </div>
    </div>
  )
}
