import { createInertiaApp } from '@inertiajs/vue3'
import { createApp, h } from 'vue'
import type { DefineComponent } from 'vue'
import '../css/workspace.css'

const pages = import.meta.glob<{ default: DefineComponent }>('./pages/*.vue', { eager: true })

const agentPages = import.meta.glob<{ default: DefineComponent }>('../../workspace/pages/*.vue', { eager: true })

void createInertiaApp({
  title: (title) => title ? title + ' · Fielddesk' : 'Fielddesk',
  resolve: (name) => {
    const page = name.startsWith('Agent/')
      ? agentPages['../../workspace/pages/' + name.slice('Agent/'.length) + '.vue']
      : pages['./pages/' + name + '.vue']
    if (!page) throw new Error('Unknown Inertia page: ' + name)
    return page.default
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) }).use(plugin).mount(el)
  },
})
