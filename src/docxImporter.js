import JSZip from 'jszip'

const NS='http://schemas.openxmlformats.org/wordprocessingml/2006/main'
const textOf=p=>Array.from(p.getElementsByTagNameNS(NS,'t')).map(n=>n.textContent||'').join('')
const runIsTitle=r=>{
  const props=r.getElementsByTagNameNS(NS,'rPr')[0]
  if(!props)return false
  const bold=props.getElementsByTagNameNS(NS,'b')[0]
  const underline=props.getElementsByTagNameNS(NS,'u')[0]
  if(!bold||!underline)return false
  const bVal=bold.getAttributeNS(NS,'val')||bold.getAttribute('w:val')||bold.getAttribute('val')
  const uVal=underline.getAttributeNS(NS,'val')||underline.getAttribute('w:val')||underline.getAttribute('val')
  return bVal!=='0'&&bVal!=='false'&&uVal!=='none'&&uVal!=='0'
}
const paragraphIsTitle=p=>{
  const runs=Array.from(p.getElementsByTagNameNS(NS,'r')).filter(r=>textOf(r).trim())
  return runs.length>0&&runs.every(runIsTitle)
}
export async function parseDocx(file){
 const zip=await JSZip.loadAsync(file)
 const entry=zip.file('word/document.xml')
 if(!entry)throw new Error('Arquivo DOCX inválido.')
 const xml=await entry.async('string')
 const doc=new DOMParser().parseFromString(xml,'application/xml')
 if(doc.querySelector('parsererror'))throw new Error('Não foi possível interpretar o documento.')
 const paragraphs=Array.from(doc.getElementsByTagNameNS(NS,'p'))
 const items=[];let current=null
 for(const p of paragraphs){
   const text=textOf(p).trim()
   if(!text)continue
   if(paragraphIsTitle(p)){
     if(current&&current.content.trim())items.push(current)
     current={title:text,content:''}
   }else if(current){current.content+=(current.content?'\n':'')+text}
 }
 if(current&&current.content.trim())items.push(current)
 return items
}
