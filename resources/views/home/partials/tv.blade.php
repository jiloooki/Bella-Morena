@if(count($listings['tv']) > 0)
<x-ui.home-list :listings="$listings['tv']" :module="$module" layout="tv" :heading="__('Popular TV Shows')" card="post" />
@endif
