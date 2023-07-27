package main

import (
	"context"
	"flag"
	"fmt"
	"log"
	"strings"
	"time"

	"github.com/PuerkitoBio/goquery"
	"github.com/chromedp/chromedp"
)

func main() {
	// Define command-line flags
	urlFlag := flag.String("url", "", "Instagram post URL")
	flag.Parse()

	if *urlFlag == "" {
		log.Fatal("Please provide the Instagram post URL using the -url flag")
	}

	if !strings.Contains(*urlFlag, "/p/") {
		log.Fatal("Invalid URL: Please provide a valid Instagram post URL")
	}

	// Create a new context for the timeout
	ctx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
	defer cancel()

	// Create a new context for the chromedp actions
	chromectx, cancelChrome := chromedp.NewContext(ctx)
	defer cancelChrome()

	var htmlContent string
	err := chromedp.Run(chromectx,
		chromedp.Navigate(*urlFlag),
		chromedp.WaitVisible(`article`, chromedp.ByQuery),
		chromedp.Sleep(1*time.Second), // Add a short sleep to allow page content to load
		chromedp.OuterHTML(`article`, &htmlContent, chromedp.ByQuery),
	)

	if err != nil {
		if strings.Contains(err.Error(), "context deadline exceeded") {
			log.Println("Timeout: The request took too long to complete")
		} else {
			log.Println("Error fetching data from Instagram:", err)
		}
		return
	}

	// Extract the username from the HTML content using a simple parser
	doc, err := goquery.NewDocumentFromReader(strings.NewReader(htmlContent))
	if err != nil {
		log.Println("Error parsing Instagram HTML response:", err)
		return
	}

	// Check if the profile is private
	if strings.Contains(htmlContent, "Sorry, this page isn't available.") {
		fmt.Println("Profile is private")
		return
	}

	var username string
	doc.Find("button:contains('Follow')").Each(func(i int, s *goquery.Selection) {
		aTag := s.Parent().Parent().Find("a")
		href, exists := aTag.Attr("href")
		if exists && strings.HasPrefix(href, "/") {
			username = strings.TrimPrefix(href, "/")
			username = strings.TrimSuffix(username, "/")
			return
		}
	})

	fmt.Println(username)
}