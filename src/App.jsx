import { useMemo, useState } from 'react'
import { Search, LayoutDashboard, MessageSquareText, FolderTree, Star, Upload, Settings, Plus, Copy, Eye, Pencil, Menu, X, ChevronRight } from 'lucide-react'

const initialResponses = [
  { id:1, title:'Chamado de venda SGS', category:'Comercial › SGS', preview:'Modelo utilizado para abertura de chamado de venda no SGS.', content:'Prezados, boa tarde.\n\nSegue abaixo o modelo de resposta para abertura do chamado de venda SGS. Confira os dados do cliente antes do envio.', tags:['sgs','venda','chamado'], favorite:false },
  { id:2, title:'Cancelamento PCLAB ONLINE', category:'PCLAB Online › Cancelamento', preview:'Orientações e modelo de resposta para solicitações de cancelamento.', content:'Prezado(a), boa tarde.\n\nRecebemos sua solicitação de cancelamento. Seguem as orientações necessárias para prosseguirmos com o atendimento.', tags:['pclab','cancelamento'], favorite:true },
  { id:3, title:'Resposta integração de sistemas: API', category:'Integrações › API', preview:'Resposta padrão com orientações sobre integração via API.', content:'Prezado(a), boa tarde.\n\nPara integração via API, é necessário validar os dados de acesso e os endpoints disponíveis para o serviço contratado.', tags:['api','integração'], favorite:false },
  { id:4, title:'Chamado SGS - Comunicação via API', category:'Integrações › API', preview:'Procedimento para chamados relacionados ao consumo de dados via API.', content:'Prezados, boa tarde.\n\nPara análise da comunicação via API, encaminhe os dados do laboratório e os detalhes da requisição realizada.', tags:['sgs','api'], favorite:true },
]

const nav = [
  [LayoutDashboard,'Início'], [MessageSquareText,'Respostas'], [FolderTree,'Categorias'], [Star,'Favoritos'], [Upload,'Importar Word']
]

export default function App(){
  const [responses,setResponses]=useState(initialResponses)
  const [query,setQuery]=useState('')
  const [active,setActive]=useState('Respostas')
  const [selected,setSelected]=useState(null)
  const [mobile,setMobile]=useState(false)
  const [toast,setToast]=useState('')

  const filtered=useMemo(()=>responses.filter(r=>{
    const match=[r.title,r.category,r.preview,r.content,...r.tags].join(' ').toLowerCase().includes(query.toLowerCase())
    return match && (active!=='Favoritos'||r.favorite)
  }),[responses,query,active])

  const copy=(text)=>{navigator.clipboard?.writeText(text);setToast('Resposta copiada para a área de transferência');setTimeout(()=>setToast(''),1800)}
  const toggleFav=id=>setResponses(v=>v.map(r=>r.id===id?{...r,favorite:!r.favorite}:r))

  return <div className="min-h-screen bg-slate-50 text-slate-900">
    <aside className={`${mobile?'translate-x-0':'-translate-x-full'} fixed inset-y-0 left-0 z-40 w-72 border-r border-slate-200 bg-slate-950 text-white transition-transform lg:translate-x-0`}>
      <div className="flex h-20 items-center justify-between border-b border-white/10 px-6"><div><div className="text-lg font-bold">Central de Respostas</div><div className="text-xs text-slate-400">Base de conhecimento</div></div><button onClick={()=>setMobile(false)} className="lg:hidden"><X size={20}/></button></div>
      <nav className="p-4">{nav.map(([Icon,label])=><button key={label} onClick={()=>{setActive(label);setMobile(false)}} className={`mb-1 flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium ${active===label?'bg-blue-600 text-white':'text-slate-300 hover:bg-white/10 hover:text-white'}`}><Icon size={19}/>{label}{label==='Respostas'&&<span className="ml-auto rounded-full bg-white/10 px-2 py-0.5 text-xs">{responses.length}</span>}</button>)}</nav>
      <div className="absolute bottom-0 w-full border-t border-white/10 p-4"><button className="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm text-slate-300 hover:bg-white/10"><Settings size={19}/>Configurações</button><div className="mt-3 flex items-center gap-3 px-4 py-2"><div className="grid h-9 w-9 place-items-center rounded-full bg-blue-600 text-sm font-bold">RD</div><div><div className="text-sm font-semibold">Raphael Dias</div><div className="text-xs text-slate-400">Administrador</div></div></div></div>
    </aside>

    <main className="lg:pl-72">
      <div className="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">
        <div className="mb-6 flex items-center gap-3 lg:hidden"><button onClick={()=>setMobile(true)} className="rounded-xl border bg-white p-2.5"><Menu size={21}/></button><span className="font-bold">Central de Respostas</span></div>
        <header className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><div className="mb-2 text-sm font-medium text-blue-600">BASE DE CONHECIMENTO</div><h1 className="text-3xl font-bold tracking-tight">{active}</h1><p className="mt-2 text-slate-500">Encontre rapidamente a resposta que precisa para o atendimento.</p></div><button className="flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"><Plus size={18}/>Nova resposta</button></header>

        <section className="mt-7 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div className="relative"><Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={20}/><input value={query} onChange={e=>setQuery(e.target.value)} placeholder="Pesquisar título, conteúdo ou palavra-chave..." className="w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-12 pr-4 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100"/></div></section>

        <div className="mt-6 flex flex-wrap items-center justify-between gap-3"><div className="flex gap-2"><button onClick={()=>setActive('Respostas')} className={`rounded-lg px-3 py-2 text-sm font-medium ${active==='Respostas'?'bg-slate-900 text-white':'border bg-white text-slate-600'}`}>Todas</button><button onClick={()=>setActive('Favoritos')} className={`rounded-lg px-3 py-2 text-sm font-medium ${active==='Favoritos'?'bg-slate-900 text-white':'border bg-white text-slate-600'}`}>Favoritas</button></div><span className="text-sm text-slate-500">{filtered.length} respostas encontradas</span></div>

        <section className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div className="hidden grid-cols-[1fr_220px_140px] border-b bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 md:grid"><span>Resposta</span><span>Categoria</span><span className="text-right">Ações</span></div>
          {filtered.map(r=><div key={r.id} className="grid gap-3 border-b border-slate-100 p-5 last:border-0 md:grid-cols-[1fr_220px_140px] md:items-center">
            <div className="min-w-0"><div className="flex items-center gap-2"><button onClick={()=>toggleFav(r.id)} className={r.favorite?'text-amber-500':'text-slate-300'}><Star size={18} fill={r.favorite?'currentColor':'none'}/></button><h3 className="truncate font-semibold">{r.title}</h3></div><p className="mt-1.5 line-clamp-1 pl-7 text-sm text-slate-500">{r.preview}</p><div className="mt-2 flex flex-wrap gap-1.5 pl-7">{r.tags.map(t=><span key={t} className="rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-500">#{t}</span>)}</div></div>
            <div className="text-sm text-slate-500">{r.category}</div>
            <div className="flex justify-start gap-1 md:justify-end"><button onClick={()=>copy(r.content)} title="Copiar" className="rounded-lg p-2.5 text-slate-500 hover:bg-blue-50 hover:text-blue-600"><Copy size={18}/></button><button onClick={()=>setSelected(r)} title="Visualizar" className="rounded-lg p-2.5 text-slate-500 hover:bg-slate-100"><Eye size={18}/></button><button title="Editar" className="rounded-lg p-2.5 text-slate-500 hover:bg-slate-100"><Pencil size={18}/></button></div>
          </div>)}
          {!filtered.length&&<div className="p-12 text-center text-sm text-slate-500">Nenhuma resposta encontrada.</div>}
        </section>
      </div>
    </main>

    {selected&&<><div onClick={()=>setSelected(null)} className="fixed inset-0 z-40 bg-slate-950/35 backdrop-blur-[2px]"/><aside className="fixed inset-y-0 right-0 z-50 flex w-full max-w-xl flex-col bg-white shadow-2xl"><div className="border-b p-6"><div className="flex items-start justify-between gap-4"><div><div className="text-xs font-semibold uppercase tracking-wide text-blue-600">{selected.category}</div><h2 className="mt-2 text-xl font-bold">{selected.title}</h2></div><button onClick={()=>setSelected(null)} className="rounded-lg border p-2"><X size={19}/></button></div></div><div className="flex-1 overflow-y-auto p-6"><div className="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Resposta</div><div className="whitespace-pre-line rounded-2xl bg-slate-50 p-5 text-sm leading-7 text-slate-700">{selected.content}</div><div className="mt-5 flex flex-wrap gap-2">{selected.tags.map(t=><span key={t} className="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700">#{t}</span>)}</div></div><div className="border-t p-5"><button onClick={()=>copy(selected.content)} className="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3.5 font-semibold text-white"><Copy size={18}/>Copiar resposta</button><div className="mt-3 flex items-center justify-between"><button onClick={()=>toggleFav(selected.id)} className="flex items-center gap-2 text-sm text-slate-600"><Star size={17}/>Favoritar</button><button className="flex items-center gap-1 text-sm font-medium text-slate-600">Editar <ChevronRight size={16}/></button></div></div></aside></>}
    {toast&&<div className="fixed bottom-5 left-1/2 z-[60] -translate-x-1/2 rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white shadow-xl">{toast}</div>}
  </div>
}
