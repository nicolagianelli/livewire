import { on } from '@/hooks'

on('commit', ({ component, commit: payload, respond }) => {
    component.reactive.__pendingCalls = payload.calls;
    component.reactive.__pendingUpdates = payload.updates;
    respond(() => {
        delete component.reactive.__pendingCalls
        delete component.reactive.__pendingUpdates
    })
})
