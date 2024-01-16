// .storybook/preview.js
/** @type { import('@storybook/server').Preview } */
const preview = {
  globals: {
    drupalTheme: 'a11ytalks',
    supportedDrupalThemes: {
      a11ytalks: { title: 'A11y Talks'},
      umami: {title: 'Umami'},
      claro: {title: 'Claro'},
    },
  },
  parameters: {
    server: {
      // Replace this with your Drupal site URL, or an environment variable.
      url: 'https://a11ytalks-drupal.ddev.site',
    },
    actions: { argTypesRegex: "^on[A-Z].*" },
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/i,
      },
    },
  },
  // ...
};

export default preview;
