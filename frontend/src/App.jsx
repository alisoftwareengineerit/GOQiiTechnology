import React, { useState } from 'react'
import Login from './components/Login'
import TaskManager from './components/TaskManager'
export default function App(){
  const [token,setToken]=useState(localStorage.getItem('token'))
  function handleLogin(t){
    localStorage.setItem('token',t)
    setToken(t)
  }
  function handleLogout(){
    localStorage.removeItem('token')
    setToken(null)
  }
  return token ? <TaskManager token={token} onLogout={handleLogout}/> : <Login onLogin={handleLogin}/>
}
