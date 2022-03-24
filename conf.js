jsproxy_config({
  // 当前配置的版本（记录在日志中，用于排查问题）
  // 每次修改配置，该值需要增加，否则不会生效。
  // 默认每隔 5 分钟自动下载配置，若想立即验证，可通过隐私模式访问。
  ver: '100',

  // 通过 CDN 加速常用网站的静态资源（实验中）
  static_boost: {
    enable: true,
    ver: 60
  },

  // 节点配置
  node_map: {
 
    'mysite': {
      label: 'local',
      lines: {
        [location.host]: 1,
      }
    },
    // 该节点用于加载大体积的静态资源
    'cfworker': {
      label: '',
      hidden: true,
      lines: {
        // 收费版（高权重）
        'node-cfworker.etherdream.com': 4,

        // 免费版（低权重，分摊一些成本）
        // 每个账号每天 10 万次免费请求，但有频率限制
        'b.007.workers.dev': 1,
        'b.hehe.workers.dev': 1,
        'b.lulu.workers.dev': 1,
        'b.jsproxy.workers.dev': 1,
      }
    }
  },

  /**
   * 默认节点
   */
  // node_default: 'mysite',
  node_default: /jsproxy-demo\.\w+$/.test(location.host) ? 'demo-hk' : 'mysite',

  /**
   * 加速节点
   */
  node_acc: 'cfworker',

  /**
   * 静态资源 CDN 地址
   * 用于加速 `assets` 目录中的资源访问
   */
  assets_cdn: 'https://cdn.jsdelivr.net/gh/pixcati/JSproxy_php@master/assets/',
  
  // 本地测试时打开，否则访问的是线上的
//   assets_cdn: 'assets/',

  // 首页路径
  index_path: 'index_v3.html',

  // 支持 CORS 的站点列表（实验中...）
  direct_host_list: 'cors_v1.txt',

  /**
   * 自定义注入页面的 HTML
   */
  inject_html: '<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/css/mdui.css"/><style type="text/css">.miniright{margin:0 -25px;text-align:right}.miniright .minifixed{position:fixed;top:0;z-index:100;display:inline-block;height:100vh}.miniright .minitool{position:absolute;bottom:30px;left:20px;width:56px}.miniright .minitool button{margin-top:20px}.miniright .minitool .mdui-fab{background:#fff}.miniright .minifixed{top:auto;right:0;bottom:0;height:auto}.miniright .minitool{right:20px;left:auto}</style><div class="miniright"><div class="minifixed"><div class="minitool"><button class="mdui-fab mdui-ripple" onclick="backhome()"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#333333" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 2v6h6M2.66 15.57a10 10 0 1 0 .57-8.38"/></svg></button></div></div></div><script>function backhome(){window.history.back(-10)}</script>',

  /**
   * URL 自定义处理（设计中）
   */
  url_handler: {
    'https://www.baidu.com/img/baidu_resultlogo@2.png': {
      replace: 'https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_92x30dp.png'
    },
    'https://www.pornhub.com/': {
      content: '<h1>暂不准许访问该网站</h1>'
    },
    'https://www.xvideos.com/': {
      redir: 'https://189.cn/'
    },
  }
})
