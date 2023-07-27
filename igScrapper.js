const puppeteer = require("puppeteer");
const { program } = require("commander");

program
  .version("1.0.0")
  .arguments("<url>")
  .action(async (url) => {
    const username = await scrapeInstagramUsername(url, {
      headless: "new",
      args: ["--no-sandbox"],
    });
    console.log(username);
  })
  .parse(process.argv);

async function scrapeInstagramUsername(url, options) {
  try {
    const browser = await puppeteer.launch(options);
    const page = await browser.newPage();

    // Emulate a real user agent to avoid detection
    await page.setUserAgent(
      "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3"
    );

    await page.goto(url, { waitUntil: "networkidle2" });

    const isFollowButton = await page.evaluate(() => {
      const buttons = Array.from(document.querySelectorAll("button"));
      return buttons.some((button) => button.textContent.includes("Follow"));
    });

    if (!isFollowButton) {
      console.log("Profile is private or follow button not found.");
      await browser.close();
      process.exit(0);
    }

    const username = await page.evaluate(() => {
      const followButton = Array.from(document.querySelectorAll("button")).find(
        (button) => button.textContent.includes("Follow")
      );

      if (followButton) {
        const aTag =
          followButton.parentElement.parentElement.querySelector("a");
        return aTag
          ? aTag.getAttribute("href").replace("/", "").replace("/", "")
          : null;
      }
      return null;
    });

    await browser.close();
    return username;
  } catch (err) {
    console.error("Error fetching data from Instagram:", err);
    process.exit(1);
  }
}
