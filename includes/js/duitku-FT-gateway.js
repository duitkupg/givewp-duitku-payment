/**
 * Start with a Self-Executing Anonymous Function (IIFE) to avoid polluting and conflicting with the global namespace (encapsulation).
 * @see https://developer.mozilla.org/en-US/docs/Glossary/IIFE
 *
 * This won't be necessary if you're using a build system like webpack.
 */
(() => {
  let settings = {};
  /**
   * Example of rendering gateway fields (without jsx).
   *
   * This renders a simple div with a label and input.
   *
   * @see https://react.dev/reference/react/createElement
   */
  function DuitkuGatewayFields() {
    return window.wp.element.createElement(
      "div",
      {
        className: "duitku-duitku-retail-help-text",
      },
      window.wp.element.createElement(
        "p",
        {
          style: { marginBottom: 0 },
        },
        settings.message ||
          "You will be redirected to Duitku Duitku Retail Payment Gateway."
      ),
      window.wp.element.createElement(
        "img",
        {
          style: { 
            display: "block",
            margin: "0 auto",
            marginBottom: 0,
            width : 150,
            height : 50,
           },
           src : "https://images.duitku.com/hotlink-ok/RETAIL.PNG",
           alt : "Duitku Retail Logo",
        },
      ),
    );
  }

  /**
   * Example of a front-end gateway object.
   */
  const DuitkuGateway = {
    id: "FT",
    initialize() {
      settings = this.settings;
    },
    Fields() {
      return window.wp.element.createElement(DuitkuGatewayFields);
    },
  };

  /**
   * The final step is to register the front-end gateway with GiveWP.
   */
  window.givewp.gateways.register(DuitkuGateway);
})();
