---
title: "LLMs"
date: 2026-10-18T00:00:00Z
---

## Table of Contents

1.  [What Even Is a Language Model](#heading-1)
2.  [Talking to Models Online](#heading-2)
3.  [Tokens, Context, and Sampling](#heading-3)
4.  [Running a Model Locally](#heading-4)
5.  [Serving the Model](#heading-5)
6.  [What Hardware Do You Actually Need](#heading-6)
7.  [Local or Cloud](#heading-7)
8.  [Giving the Model Hands](#heading-8)
9.  [A Whole Agent](#heading-9)
10. [Bang! Bang! She-Bang!](#heading-10)
11. [Agent Tools and Free Providers](#heading-11)
12. [Wrapping Up](#heading-12)

## What Even Is a Language Model {#heading-1}

Before we do anything else, it is worth being precise about what a language model actually is, because the marketing around these things has made a genuinely simple idea sound impossibly complicated.

Strip away the branding and a language model is a very large function that does one thing: given some text, it produces a number predicting what the next piece of text should be. That is it. Everything else, every poem, every bug, every confident lie, comes from repeating that one operation over and over.

The reason it can do anything at all is that it has read an enormous amount of text. Not understood, in the way that you and I understand things, but absorbed. During training the model is shown billions of fragments of text and asked to guess what comes next. When it guesses wrong, the internal numbers are nudged slightly in the direction that would have made the guess right. Do that a few trillion times and what you end up with is a giant lookup table that has, as a side effect, absorbed an enormous amount of statistical structure about how language, and about the world that language describes, tends to work.

This is why people argue about whether it "really" understands anything. I am going to deliberately sit on the fence here, because the argument is not something that will be settled in this guide. What is not in dispute is that the model has learned a great deal of structure that is useful, and that it is applying that structure in a way that is very good at producing text which is locally plausible.

One consequence of this is worth internalizing early, because it explains most of the strange behaviour you will eventually run into. The model is optimizing for producing plausible text, not for producing true text. Those are different goals. A confident false statement can be extremely plausible if the surrounding pattern is a pattern where confident statements are given, and the model has no separate internal ledger marked "is this true". It is not lying to you, exactly, because lying requires knowing the difference. It is doing the one thing it was built to do, and that thing occasionally produces things that are wrong.

The second consequence is that a model has no memory of you unless it is written down. Every conversation starts from nothing. If the model knows your name, it is because the name is in the context, right now, in this request. This is why the same model can be brilliant in one conversation and completely amnesiac in the next, and it is the reason that the idea of a "context window" ends up mattering so much more than you might expect.

The third consequence is that language models do not run code, compute things, or look things up, unless you build that in yourself. They emit text. That is the whole interface. Everything in the rest of this guide, from quantization to tool calling to the agent loop, is an attempt to work around that one limitation, and once you see it that way the field becomes much less mysterious.

## Talking to Models Online {#heading-2}

The sensible order of operations is to use one of the hosted models before you try to run one yourself, for exactly the same reason the bash guide started at the terminal rather than at the C compiler. You want to understand what the thing does before you start fighting with it.

There are several of these. ChatGPT, Claude, and Gemini all have web interfaces you can open in a browser and start typing into immediately, with no installation, no account setup beyond an email address, and no cost until you start building something. Spend a few days in one of them. Learn the failure modes. Notice what it is bad at, which in particular means finding out what it is bad at for *your* particular kind of work, because it varies enormously.

The thing that trips people up when they move from the web interface to code is that the web interface is not the API, and the difference is not cosmetic. In the browser, the model has a system prompt you cannot see, a conversation history you cannot edit, and a chunk of post-processing on the output. When you call an API you get none of that. You send a list of messages and you get back a list of messages. There is no magic, no memory, and no hidden state. Every single thing the model knows has to be in the request.

Here is the whole shape of it, using the OpenAI chat completions format, which has become the closest thing to a common language between model providers:

```python
import openai

client = openai.OpenAI(api_key="sk-...")

completion = client.chat.completions.create(
    model="gpt-4o-mini",
    messages=[
        {"role": "system", "content": "You are a terse assistant."},
        {"role": "user", "content": "Write a limerick about python exceptions"},
    ],
)

print(completion.choices[0].message.content)
```

Three things are worth staring at, because they are the same three things you will look at later when you are pointing this exact code at a model running on your own machine.

The `model` field is a string naming which model you want. With a hosted provider it selects something from their catalogue. With a local server it is almost always ignored entirely, because there is only one model loaded and it is obviously the one you meant.

The `messages` list is the entire conversation, every time. The word "completion" in the endpoint name is a leftover from an older API and the endpoint is a bit of a misnomer now: you are not completing a string, you are having a conversation that you happen to be responsible for resending in full every turn. This is the single most important thing to internalize about the API, and it is the reason the context window keeps coming back.

The response comes back as `choices[0].message.content`, and at this point it is just a string. When we get to tools in section eight, that message will also be able to carry structured requests for action, and that is the whole difference between a chatbot and an agent.

## Tokens, Context, and Sampling {#heading-3}

Two ideas stand between you and a working local setup, and both of them come with vocabulary that is worth pinning down now rather than discovering painfully later.

The first is tokens. A model does not read characters or words. It reads tokens, which are chunks of text, usually somewhere between a quarter of a word and a word and a half. The word "unbelievable" might be one token, three tokens, or two, depending on how common it is in the training data. Common words are short. Weird words get chopped into pieces.

This matters for two practical reasons. First, pricing and memory are both measured in tokens rather than in words, so when someone tells you a context window is 128k they mean 128 thousand tokens, which is roughly 90,000 English words, which is a novel. Second, and much more interestingly, the model only ever sees tokens, so it cannot reliably count characters, and asking it how many r's are in a word is famously hopeless.

The context window is the fixed-size window of tokens the model can attend to at once. Everything about the current conversation has to fit inside it, including the system prompt, the entire history, and everything the model is about to write. When the conversation outgrows the window something has to give, and the usual answer is that the oldest turns get dropped. That is why a long chat with a model will sometimes appear to forget something you told it an hour ago, and it is not a bug, it is the window doing what windows do.

The second idea is sampling. At every step, the model produces not one token but a distribution over the whole vocabulary, a probability for every possible next token. Something then has to pick one, and the way it picks is where most of the "creativity controls" live.

Temperature is the main one. At zero, the model always takes the single most likely token, which makes the output deterministic and repeatable but also flat and prone to loops. Turn it up and lower-probability tokens start getting picked, which is what makes the output varied, surprising, and eventually incoherent. The defaults in llama.cpp, which you can see any time with `--help`, are a temperature of 0.80, a top-k of 40, a top-p of 0.95 and a min-p of 0.05. If you take one thing from this section, take this: those four numbers together are the entire reason the same question gives you a different answer every time.

The other two worth knowing are top-p and repetition penalties. Top-p truncates the distribution, keeping only the smallest set of tokens whose probabilities add up to the given fraction, so the model never even considers the long tail of unlikely tokens. Repetition penalties push down the probability of tokens it has recently used, which mostly just prevents the model from getting stuck in a loop emitting the same phrase over and over.

## Running a Model Locally {#heading-4}

Now the interesting part. The tool we are going to use is **llama.cpp**, which is a plain C and C++ implementation of model inference with essentially no dependencies. It started life as a way to run a model on a Raspberry Pi, and it retains that DNA: the whole design goal is to run on whatever hardware you happen to have, including hardware that has no GPU at all.

There are three ways to get it, in decreasing order of how much you will learn and increasing order of convenience.

The quickest is the official installer, which is a single line:

```bash
curl -LsSf https://llama.app/install.sh | sh
```

The middle way is grabbing a prebuilt binary from the releases page, which is also what you want if you do not want a compiler installed on your machine.

The way this guide is going to do it is by building from source, because it is the only one of the three where you can turn on GPU support properly.

```bash
git clone https://github.com/ggml-org/llama.cpp
cd llama.cpp
cmake -B build
cmake --build build --config Release -j 8
```

That builds a plain CPU version, which genuinely works and is what you want for a first run. The `-j 8` runs eight compiler jobs in parallel, which on a modern machine takes the build from an uncomfortable coffee break down to a tolerable one.

For GPU acceleration you reconfigure with the backend for your hardware:

```bash
cmake -B build -DGGML_CUDA=ON        # NVIDIA
cmake -B build -DGGML_HIP=ON          # AMD, via ROCm
cmake -B build -DGGML_VULKAN=ON      # almost anything, including Intel and AMD
cmake --build build --config Release -j 8
```

On a Mac, Metal is already enabled by default and you do not need to ask for it. The backend list is genuinely long, including Vulkan, SYCL for Intel, ZenDNN for AMD server CPUs, and KleidiAI for Arm. You do not need to think about most of them. The one that is worth knowing is **Vulkan**, because it is the portable one. If you have a GPU and it is not an NVIDIA card, Vulkan is very likely to just work, which is a much better outcome than a long afternoon of driver archaeology.

A useful habit while you are setting this up is to ask the binary what it can see:

```bash
./build/bin/llama-cli --list-devices
```

Whatever comes back is what you can offload to.

### The model file

You cannot just download a model from anywhere. The weights need to be in a format llama.cpp can read, and that format is called **GGUF**. You can find GGUF versions of most popular models on Hugging Face, often from community publishers.

Happily, you barely have to think about this. llama.cpp can download a model for you straight from a Hugging Face repository, and will pick a sensible quantization if you do not name one:

```bash
./build/bin/llama-cli -hf bartowski/Qwen2.5-7B-Instruct-GGUF
```

That downloads the model, caches it, and starts a chat. The `:Q4_K_M` suffix is optional and is the default when a quant of that name exists.

### Quantization

A 7B parameter model in full precision is roughly fourteen gigabytes, which is a lot to ask of a laptop. **Quantization** means storing the weights at lower precision, rounding each one to a smaller number of bits. Q8_0 keeps eight bits per weight, Q6_K about six and a half, Q5_K_M about five and a half, Q4_K_M about five, and Q2_K goes down to two bits and is genuinely unpleasant to talk to.

Here are real file sizes, so the trade-off is concrete rather than theoretical:

| Model | Q4_K_M | Q5_K_M | Q6_K | Q8_0 |
|---|---|---|---|---|
| Qwen2.5-3B-Instruct | 1.93 GB | — | — | 3.29 GB |
| Qwen2.5-7B-Instruct | 4.68 GB | 5.44 GB | 6.25 GB | 8.10 GB |
| Llama-3.1-8B-Instruct | 4.92 GB | 5.73 GB | 6.60 GB | 8.54 GB |
| Qwen3-8B | 5.03 GB | 5.85 GB | 6.73 GB | 8.71 GB |
| Llama-3.3-70B-Instruct | 42.52 GB | — | — | — |

Notice how little the architecture matters. The three 7-8B models sit within about 10% of each other at every quant, which is what makes the sizing in section six something you can calculate rather than look up.

A word of caution about the defaults, since this is where most people end up under-provisioning their hardware. llama.cpp reaches for Q4_K_M when you do not name a quant, and Q4_K_M is what most guides recommend, because most guides are written for the person with the least memory. But Q4 is a compromise you make when you are short on memory, not the best available setting. An 8B model at Q8 is about 8.6 GB, and the quality difference from the unquantized original is small enough that you should think of Q8 as "the model" rather than as a degraded version of it. Reach for Q4 when 4.9 GB is genuinely all you have.

Below Q4 the model starts losing factual accuracy in a way you will notice, and the size savings stop being worth it.

### Running it

```bash
./build/bin/llama-cli -hf bartowski/Qwen2.5-7B-Instruct-GGUF -c 8192 -ngl 99
```

The `-c 8192` sets the context window to 8192 tokens. The `-ngl 99` asks for all 99 layers of this model to be put on the GPU. Since current versions default to fitting the model automatically, you will often find you do not need to pass this at all, but it is the flag to reach for when you want to be explicit, and `-ngl 0` to force everything back onto the CPU when you are debugging something odd.

You will also notice, once you start playing, that llama.cpp prints timing information after each reply. The two numbers that matter are prompt processing speed and generation speed, both in tokens per second. Generation speed, typically somewhere between 5 and 60 tokens per second depending on your hardware and model size, is the number that determines whether the conversation feels responsive. Prompt processing speed, which is much higher, is how fast it can read a large context.

## Serving the Model {#heading-5}

Talking to a model in a terminal is fine for experimenting and useless for anything else. The next step is to run it as a server, so that other programs can talk to it over HTTP.

```bash
./build/bin/llama-server -hf bartowski/Qwen2.5-7B-Instruct-GGUF -c 8192 -np 4
```

The port this listens on is **9931**, and it binds to `127.0.0.1` by default, so it is not reachable from your network unless you deliberately change that with `--host`. That default is worth keeping in mind for section nine.

Point a browser at it and you get a built-in chat interface, which is genuinely pleasant and is the fastest way to have a normal conversation with a local model.

Now the important part: **the server speaks the OpenAI API**. The endpoint `/v1/chat/completions` takes exactly the request format from section two, and it accepts the API key `sk-no-key-required` as a formality. That means the Python code you wrote earlier, and indeed any OpenAI client library in any language, works against it by changing one line:

```python
import openai

client = openai.OpenAI(
    base_url="http://localhost:9931/v1",
    api_key="sk-no-key-required",
)

completion = client.chat.completions.create(
    model="local-model",
    messages=[{"role": "user", "content": "Say hello."}],
)

print(completion.choices[0].message.content)
```

`model` is ignored here, because there is only one model loaded. This is the single most useful fact in the guide: because llama.cpp implements the same interface, you can point your existing code at a local model by changing a URL, and nothing else. You develop against a big hosted model, it works, and then you swap the base URL and run it on your own hardware with no code changes at all.

The `-np 4` flag sets the number of **slots**, which are independent conversations the server can hold at once, so four people can use the same model simultaneously. Continuous batching, which is on by default, is what makes that efficient: rather than running one person at a time, the server interleaves their requests so the GPU is never idle.

There are a few more endpoints worth knowing about, mostly because they make debugging easier. `/v1/models` tells you what is loaded and what it can do. `/props` shows every setting and metadata the model carries, including which chat template it uses, which matters enormously in the next section. And token counting endpoints let you check how big a prompt is before you send it.

If your installation gives you a unified `llama` command, as the current project README shows, then `llama cli` and `llama serve` are the same two programs. The names used throughout this guide are the classic per-tool binaries.

## What Hardware Do You Actually Need {#heading-6}

This is the question everybody has and almost nobody answers properly, so let us do it with arithmetic rather than vibes.

The rule that governs everything is this: **the model has to fit in memory, and nothing else is negotiable.** Not the code, not the operating system, not your other applications. A model that does not fit does not run slowly, it does not run at all.

Total memory needed is the model file, plus the KV cache, plus roughly a gigabyte of working buffer that llama.cpp needs for intermediate activations. The model file is the easy part, because it obeys a very simple rule.

### Bytes per parameter

Quantization stores weights at a reduced number of bits each. What that works out to in bytes on disk is remarkably consistent across architectures, because "8 bits" does not care what shape the tensor is:

| Quant | Bytes per parameter | Effective bits | An 8B model |
|---|---|---|---|
| Q8_0 | ~1.07 | 8.5 | **~8.6 GB** |
| Q6_K | ~0.83 | 6.6 | ~6.6 GB |
| Q5_K_M | ~0.72 | 5.8 | ~5.8 GB |
| Q4_K_M | ~0.61 | 4.9 | ~4.9 GB |

That last column is the important one. **An 8B model at Q8 is about 8.6 GB, not 5.** It is tempting to assume quantization is what makes an 8B model small enough to run at all, and that assumption is what makes most people's hardware advice too pessimistic. If you have 16 GB of RAM, you can run an 8B model at Q8, which is very close to the unquantized original, and you should. The gap between Q8 and the original in quality is small; the gap between Q4 and Q8 is not.

The numbers are consistent enough that you can compute for any model without downloading it. Multiply the parameter count by the bytes per parameter for the quant you want:

- 3B at Q8: 3 x 1.07 = **3.2 GB**
- 14B at Q4: 14 x 0.61 = **8.5 GB**
- 32B at Q4: 32 x 0.61 = **19.5 GB**
- 70B at Q4: 70 x 0.61 = **42.7 GB** (which matches the 42.52 GB Llama 3.3 70B file almost exactly)

The slight excess over the raw bit count is scales and metadata stored alongside the weights, which is why Q8_0 comes out a little above exactly one byte per parameter rather than exactly on it.

### The KV cache

For every token in your context, the model stores a copy of the attention keys and values at every layer. The size is two times the number of layers, times the number of key-value heads, times the head dimension, times the bytes per element. At the default `f16` that is:

| Context window | Llama 3.1 8B (128 KiB/token) |
|---|---|
| 8,192 tokens | 1.0 GiB |
| 32,768 tokens | 4.0 GiB |
| 131,072 tokens | 16.0 GiB |

And here is the part that catches people, because **that rate is specific to the model**. Architectures differ a lot in how many key-value heads they use, and among models of almost identical size you get:

| Model | Layers | KV heads | Per token | @8k | @32k |
|---|---|---|---|---|---|
| Qwen2.5-7B | 28 | 4 | **56 KiB** | 0.44 GiB | 1.8 GiB |
| Llama 3.1 8B | 32 | 8 | **128 KiB** | 1.0 GiB | 4.0 GiB |
| Qwen3-8B | 36 | 8 | **144 KiB** | 1.12 GiB | 4.5 GiB |

A 2.5x spread between models you would otherwise consider interchangeable. So the useful thing to take away is not a number but the habit: check your model's own config, and do not assume a marketing figure for maximum context means anything about your machine. The context window is not free.

So that 4.92 GB Llama 3.1 8B at Q4 is really asking for about 5.9 GB at an 8k context and about 8.9 GB at 32k, and the same model at Q8 is asking for 9.6 GB and 12.5 GB respectively.

If you are memory constrained, you can quantize the cache as well as the weights, and llama.cpp exposes this directly:

```bash
./build/bin/llama-cli -m model.gguf -ctk q8_0 -ctv q8_0
```

That halves it, and `q4_0` quarters it. This is a genuinely useful trick when you are squeezed, but it comes with a real caveat: aggressive KV quantization degrades tool calling performance noticeably, and the llama.cpp documentation is explicit about this. If your goal is an agent, leave the cache at its `f16` default and find the memory somewhere else. A cheaper model at Q8 with a clean cache will beat an expensive one with a mangled one.

### What this means for your machine

Budget about 2 GB of headroom beyond the model for the OS and the compute buffer, so the figures below are what you can actually keep resident rather than what will technically load once.

**8 GB of RAM, no GPU.** Realistically about 5.5 GB to play with. A 3B model at Q8, or a 7B at Q4. It will be slow on CPU, maybe 10 to 20 tokens per second for a 3B, so it is readable but not pleasant. Fine for learning, useless for anything long.

**16 GB of RAM, no GPU.** About 13 GB usable, and this is the classic laptop tier. An 8B model at Q8 with an 8k context, comfortably. Or a 14B at Q4 if you would rather have the bigger model in lower fidelity. Expect single-digit to low-teens tokens per second, and expect it to heat up your laptop.

**A consumer GPU with 8 to 12 GB of VRAM.** This is the sweet spot, and VRAM is fully available for the model. At 8 GB you get an 8B at Q4 or a 7B at Q6. At 12 GB you can run an **8B model at Q8 entirely in VRAM**, which is the configuration most people should be aiming for, and you are looking at 40 or more tokens per second, faster than most people type.

**24 GB of VRAM, or 32 GB of system RAM.** A 20B at Q4, or a 14B at Q8, or a 32B at Q4 if you are patient with it. This is roughly where a local model stops being a compromise and starts matching a good hosted one for most everyday work.

**48 GB or more.** A 32B at Q8, or a 70B at Q4, which at 42.5 GB is the point where you need multiple GPUs or a serious amount of unified memory.

And then there is the option that changes the arithmetic entirely: **unified memory**. On Apple Silicon and on AMD systems with integrated graphics, the CPU and the GPU share the same pool of RAM. There is no VRAM to run out of, because the "VRAM" is just the system RAM. This is why llama.cpp's README describes Apple silicon as a first-class citizen, and it means a laptop with 32 GB of unified memory will run a 20B model that a gaming PC with 12 GB of dedicated VRAM cannot.

A closing note on speed, because it is the thing people most want to know and least expect. Tokens per second on CPU is bound by **memory bandwidth**, not by clock speed, and it scales close to inversely with model size. That is why a 3B on a CPU is genuinely usable and a 32B on a CPU is not, even though the second machine might be newer and faster per core. More memory bandwidth helps more than more cores.

### The partial offload escape hatch

There is one more trick that makes hardware mismatches less painful than they sound. You do not have to choose between "on the GPU" and "on the CPU". The `-ngl` flag specifies how many layers to keep in VRAM, and the rest run on the CPU:

```bash
./build/bin/llama-cli -m model.gguf -ngl 20
```

On a machine with 8 GB of VRAM and a 32 GB model, that runs. It will feel sluggish because every token has to shuttle across the bus, but it runs, and it runs *well enough* to be useful. Current versions default to fitting the model automatically, and you can control how aggressive that is with `--fit-target`. For genuinely large models on modest machines, llama.cpp also supports offloading to other machines over the network via its RPC backend, which is a genuinely absurd but completely real way to turn five old laptops into one large model.

## Local or Cloud {#heading-7}

Now the trade-offs, which are genuinely two-sided and which people routinely present as if they were not.

**In favour of the cloud.** The models are simply better. Not marginally better, but better in a way that no amount of quantizing will close. A 70B model running on a rented GPU is going to be smarter at reasoning, better at code, and far better at long documents than a 7B on your laptop, and pretending otherwise does everyone a disservice. Beyond quality, there is the matter of effort: someone else owns the hardware, someone else handles the drivers, and you can be generating text thirty seconds after deciding you want to.

There are also the things you cannot do locally at all. A hundred thousand token context, multimodal input, and a model that learned about things from last month are all cloud-only. And crucially, local inference competes with everything else on your machine. A model that saturates every core and all of your memory bandwidth is a machine that is not doing anything else while it runs, which is a real cost that does not appear in any comparison table.

**In favour of local.** Privacy is the obvious one and it is not a close call. When the text goes to a provider, the text goes to a provider. If you are working on source code you do not own, on a document you are under an NDA about, or on anything involving someone else's personal data, that conversation happened on somebody else's computer and you did not get to decide what they do with it. Running locally means the data does not leave.

Then there is cost, which is more nuanced than the "cloud is expensive" folk song. A hobbyist running a 7B locally pays nothing, forever, for unlimited tokens. A company paying per-token for millions of tokens per month is paying real money, and that number is entirely predictable rather than variable. The break-even calculation is genuinely unfun, because it depends on your utilization, and the honest summary is that a local machine wins if you use it constantly and loses badly if you use it occasionally.

Speed and latency are the next ones, and they cut against the cloud more often than people expect. A local model on your own hardware responds in well under a second. A cloud API, even a fast one, has a round trip to somebody else's datacenter and back. For interactive use, a local model that is good enough can feel *better* than a better model that takes three seconds.

The last one is longevity, and I think it is underrated. A local model is a file on your disk. It will still be there in five years, running on hardware that does not exist yet, producing the same output it produced today. A hosted model can be deprecated, renamed, quietly updated, or shut down, and when that happens the behaviour of your application changes without anyone telling you. Models are infrastructure, and infrastructure that somebody else can switch off underneath you is a category of risk that is worth taking seriously.

My own rough rule, offered with all appropriate humility: use cloud for the hard thinking and local for the bulk work. If you need the best available model to make a decision, send the hard question to a hosted API. If you need to summarize, classify, extract, or rewrite four hundred documents, run that locally and pay nothing. Most workflows turn out to be mostly the second kind.

## Giving the Model Hands {#heading-8}

A model on its own emits text. It cannot read your files, run your tests, or check whether the thing it just told you is true. This is the limitation from the end of section one, and tools are how you remove it.

The mechanism is **function calling**, and the idea behind it is simple. You describe, in a JSON structure, a list of functions your program can perform, with a name, a description, and a schema saying what arguments each one takes. You send that list along with the conversation. The model does not call anything. It *writes out* a request to call one of them, in a structured form, and your program reads that request and performs the action itself.

What comes back looks like this:

```json
{
  "choices": [
    {
      "finish_reason": "tool",
      "message": {
        "role": "assistant",
        "content": null,
        "tool_calls": [
          {
            "name": "get_current_weather",
            "arguments": "{\"location\": \"Istanbul\"}"
          }
        ]
      }
    }
  ]
}
```

Two details to notice. `finish_reason` is `"tool"`, not `"stop"`, which is how your loop knows to go and do the work rather than print the answer. And `arguments` is a **JSON string**, not a parsed object, so it needs one more round of `json.loads` before it is anything useful.

Running this against llama.cpp needs one specific flag. Tool calling requires the server to be started with `--jinja`:

```bash
./build/bin/llama-server --jinja -hf bartowski/Qwen2.5-7B-Instruct-GGUF
```

The `--jinja` flag enables the Jinja chat template system, which is what lets llama.cpp serialize tool definitions into the prompt in the exact format the model was trained to expect. Without it, the server will happily accept your tools array and the model will cheerfully ignore it.

Not every model does this well. llama.cpp has native handlers for a good number of families, including Qwen 2.5, Llama 3.x, Hermes 2 Pro and 3, Functionary, Mistral Nemo, Command R7B, GPT-OSS and FireFunction v2, and falls back to a generic format for everything else. You can see which one you got in the startup logs, and you can check whether your model even has a tool-aware template by looking at the `chat_template_tool_use` field at `/props`. If a model has no proper tool template, `--chat-template chatml` often works, and for some models llama.cpp ships an override template you can point at with `--chat-template-file`.

Two more things worth knowing. Parallel tool calls, where the model requests several actions at once, are supported by some models and disabled by default; you enable them with `"parallel_tool_calls": true` in the request body. And if you go looking for advice to make tool calling work better, you will find a lot of it that is really advice about model size. Small models are noticeably worse at deciding when to use a tool and what arguments to pass. A 1.5B model will technically support tools and will use them badly.

## A Whole Agent {#heading-9}

Take function calling and add a loop, and you have an agent. The structure is simple enough that it is worth stating plainly, because all the complexity in the field is variations on this:

1. Send the conversation and the tool list to the model.
2. If the model requests a tool, run it, append the result, and go back to step 1.
3. If it does not, print the answer and stop.

That is it. Everything an agent framework does is bookkeeping around those three lines, and you can write the whole thing in about forty lines of Python, which is what section ten does.

The reason this works at all is the thing from section one. The model cannot run anything by itself, so the loop is entirely under your program's control. The model proposes, your program disposes. That is both why agents are safe to write and why they are dangerous to write badly, because the safety boundary is entirely in your code and not at all in the model.

### llama.cpp can already do this part for you

Before writing any of it, it is worth knowing that llama.cpp has a shortcut. The server can be given built-in tools at startup:

```bash
./build/bin/llama-server --jinja -hf bartowski/Qwen2.5-7B-Instruct-GGUF \
  --tools get_info,read_file,grep_search
```

The available built-in tools are `get_info`, `read_file`, `write_file`, `edit_file`, `file_glob_search`, `grep_search` and `exec_shell_command`. With those enabled the web interface at `http://localhost:9931` becomes an agent that can search, read and edit files in the directory you started it from, with no Python written at all. It is genuinely the fastest way to see what a local agent looks like doing real work, and it is worth twenty minutes of your evening.

Now read the documentation line about this flag carefully, because it is the whole security conversation in one sentence: it is **experimental**, it is **not to be enabled in untrusted environments**, and enabling it restricts CORS to localhost. llama.cpp ships `exec_shell_command`. If you start a server with `--tools all` and you bind it to your network, you have published a machine that will run commands on request. The flag exists, and the warning exists, and the warning is not decorative.

### What "untrusted" means here

The prompt is the untrusted part, and once you have tools in the loop you need to treat what comes back from the model as data rather than as instructions.

A user types something into your agent. The model reads it. The model decides to call a tool. The model wrote the arguments to that tool call, based on your user's input. If your user is your user, that is fine. If your user is somebody else, or if the agent read a web page, or if the agent read a file that an attacker controls, then the arguments to your tool call were chosen by the attacker, and `exec_shell_command` will do exactly what they asked for.

This is prompt injection, and it is not a theoretical concern. It is the single most common way real agent systems get exploited, and the mitigations are boring: do not give the model tools it does not need, refuse tool calls that do not come from a context you control, keep the dangerous tools out of reach of untrusted input, and treat any file the model reads as tainted for the rest of the run.

## Bang! Bang! She-Bang! {#heading-10}

We have a model, a server, tools, and a loop. Let us put the loop in a file.

If you read the [python scripting guide](/cs_stuff/python_scripting/), you already know why the first line is the first line. A Python script needs a shebang and the execute bit, and everything after that is the same as it ever was.

This script runs against the local server from section five, and it is a complete agent: the loop, the tool dispatch, the result fed back into the conversation, and a hard cap on how many times it is allowed to go round.

```python
#!/usr/bin/env python3

"""A minimal tool-calling agent loop against a local llama.cpp server."""

import argparse
import json
import sys

import openai

SYSTEM_PROMPT = (
    "You are a careful assistant with access to a few local tools. "
    "Use them when they would help. When you have enough information, "
    "stop calling tools and answer."
)


def read_file(path: str) -> str:
    """Read a text file and return its contents."""
    from pathlib import Path

    target = Path(path).expanduser()
    if not target.is_file():
        return f"Error: '{path}' is not a file"
    try:
        return target.read_text(encoding="utf-8", errors="replace")
    except OSError as exc:
        return f"Error: could not read '{path}': {exc}"


def list_directory(path: str) -> str:
    """List the entries in a directory."""
    from pathlib import Path

    target = Path(path).expanduser()
    if not target.is_dir():
        return f"Error: '{path}' is not a directory"
    entries = sorted(p.name + ("/" if p.is_dir() else "") for p in target.iterdir())
    return "\n".join(entries[:200]) or "(empty directory)"


TOOLS = {
    "read_file": read_file,
    "list_directory": list_directory,
}

SCHEMAS = [
    {
        "type": "function",
        "function": {
            "name": "read_file",
            "description": "Read a UTF-8 text file from the local filesystem.",
            "parameters": {
                "type": "object",
                "properties": {
                    "path": {"type": "string", "description": "Path to the file."}
                },
                "required": ["path"],
            },
        },
    },
    {
        "type": "function",
        "function": {
            "name": "list_directory",
            "description": "List the names of the entries in a directory.",
            "parameters": {
                "type": "object",
                "properties": {
                    "path": {"type": "string", "description": "Path to the directory."}
                },
                "required": ["path"],
            },
        },
    },
]


def run_turn(client, model: str, messages: list) -> str:
    """Send the conversation and return the model's reply message."""
    completion = client.chat.completions.create(
        model=model,
        messages=messages,
        tools=SCHEMAS,
        temperature=0.2,
    )
    return completion.choices[0].message


def agent_loop(client, model: str, question: str, max_steps: int) -> int:
    messages = [
        {"role": "system", "content": SYSTEM_PROMPT},
        {"role": "user", "content": question},
    ]

    for step in range(1, max_steps + 1):
        reply = run_turn(client, model, messages)
        messages.append(reply.model_dump(exclude_none=True))

        calls = getattr(reply, "tool_calls", None)
        if not calls:
            print(reply.content)
            return 0

        for call in calls:
            name = call.function.name
            handler = TOOLS.get(name)
            if handler is None:
                result = f"Error: no such tool '{name}'"
            else:
                try:
                    args = json.loads(call.function.arguments or "{}")
                    result = handler(**args)
                except Exception as exc:
                    result = f"Error: {type(exc).__name__}: {exc}"

            print(f"[step {step}] {name} -> {result}", file=sys.stderr)
            messages.append(
                {"role": "tool", "name": name, "content": str(result)}
            )

    print("Error: agent hit the step limit without finishing", file=sys.stderr)
    return 1


def main() -> int:
    parser = argparse.ArgumentParser(description="Run a local tool-calling agent.")
    parser.add_argument("question", help="what you want the agent to do")
    parser.add_argument("--base-url", default="http://localhost:9931/v1")
    parser.add_argument("--model", default="local-model")
    parser.add_argument("--max-steps", type=int, default=8)
    args = parser.parse_args()

    client = openai.OpenAI(base_url=args.base_url, api_key="sk-no-key-required")
    return agent_loop(client, args.model, args.question, args.max_steps)


if __name__ == "__main__":
    sys.exit(main())
```

Let us go through it, in the spirit of the breakdown at the end of the other two guides:

1. `#!/usr/bin/env python3` - the shebang, unchanged since the bash guide
2. `openai` - the only dependency, and it is talking to `localhost`
3. `SCHEMAS` - the tool definitions, in the OpenAI format from section eight. This is a plain list of dicts; nothing magic is happening
4. `TOOLS` - the mapping from the name the model chose to the Python function that does the work. The model never imports anything or opens anything. It names a function, and your program decides what that name means
5. `temperature=0.2` - deliberately low. You are asking for tool calls, not creative writing, and a low temperature makes the model far more likely to produce a well-formed call rather than improvise
6. `reply.model_dump(exclude_none=True)` - converting the reply object back into plain dicts so it can go back into the message list. The `exclude_none` matters, because `content` is `null` on a tool call and sending the null back trips up some servers
7. `if not calls` - this is step 3 of the loop. If the model did not ask for a tool, it is finished, and we print the answer
8. `TOOLS.get(name)` - the critical safety line. The model picks the name; your program decides whether that name exists. A model that invents a tool gets `Error: no such tool`, not an arbitrary import
9. `handler(**args)` - actually doing the work, with the model's arguments
10. `{"role": "tool", ...}` - feeding the result back. This is what closes the loop
11. `max_steps` - the loop needs a bound. Without it a confused model will happily call tools forever, and you have built a very expensive `while True`

Note what is **not** in there. There is no `exec_shell_command`, because nothing in this conversation needed one. There is no file writing. There is no networking. Every dangerous tool is a line you do not have to write, and that is the single most effective safety measure available when you are building an agent.

Run it like any other script:

```
$ chmod +x agent.py
$ ./agent.py "what is in README.md?"
```

and watch the tool calls go by on stderr while the answer comes out on stdout.

## Agent Tools and Free Providers {#heading-11}

The loop from section nine is forty lines, and that is the entire trick. What has grown up around it, however, is a pile of genuinely capable tools that will do the loop, the tool definitions, the context management, the retries and the UI for you, and there is no reason to rebuild any of that when one of them fits.

Two worth knowing about, with genuinely different philosophies.

### OpenCode

[OpenCode](https://opencode.ai) is an open source AI coding agent. It is a terminal application, it is free, and it is built around the idea that a coding agent should be usable with any model you like rather than locked to one provider.

Installing it is the usual shell incantation:

```bash
curl -fsSL https://opencode.ai/v2/install | bash
```

Or, if you prefer a package manager, `npm i -g @opencode/cli`, `brew install anomalyco/tap/opencode-v2`, `sudo pacman -S opencode`, or `mise use -g opencode`. The source lives at [github.com/anomalyco/opencode](https://github.com/anomalyco/opencode), which was formerly under the `sst` organisation, so older links to `sst/opencode` will still resolve.

What makes it interesting is that it has two agents built in and switches between them with the `Tab` key. **build** is the default, with full access, for actually doing development work. **plan** is read-only: it refuses to edit files and asks permission before running anything, which makes it the right thing to point at a codebase you do not understand yet. There is also a `general` subagent for multi-step searches, invoked by writing `@general` in a message.

Inside the TUI, `/connect` attaches a provider and `/models` lists what you can pick. Pointing it at a model is a one-line config:

```jsonc
{
  "$schema": "https://opencode.ai/config.json",
  "model": "opencode/gpt-5.5"
}
```

### DeepSeek Harness

[DeepSeek Harness](https://deepseek.com/harness), `dsh`, takes a different approach. Rather than being a coding agent, it is an agent *harness* built on an everything-is-a-plugin architecture, where the runtime itself is assembled from plugins rather than hard-coded. That is a more interesting engineering bet than it sounds, because it is the part of an agent that is genuinely hard: permissions, tool registration, sandboxing and context handling all become things you can replace.

It is TypeScript, it runs under Node, and you can start it with:

```bash
npx @deepseek-ai/dsh web
```

which brings up a web interface, or run it from a checkout with `pnpm install`, `pnpm run build`, `pnpm dsh web`.

Two things to know before you install it. It is a **developer preview** and the README is emphatic that there will be compatibility-breaking changes, so do not build a workflow you cannot afford to rework. And it ships a safety notice that is worth reading in full, because it is unusually blunt: it has not been through a security audit, it executes model-generated code and commands, it loads third-party plugins, and sandboxing and approval prompts "do not guarantee isolation or prevent damage". Their recommended posture is to run it with least privilege, preferably in a disposable VM or container, with backups of anything it can reach.

That is good advice for every tool in this section, not just this one.

### Trying them out for free

This is the part that makes experimenting practical, and it comes with a genuine catch that deserves its own subsection rather than a footnote.

OpenCode ships a model gateway, known as Zen and now branded **Console**, at [opencode.ai/console](https://opencode.ai/console). You add credits, get an API key, and it exposes both OpenAI-compatible and Anthropic-compatible endpoints, so the same model list serves almost any agent tool. Alongside the paid models there is a set of genuinely free ones, marked `-free` in their model IDs, including several the documentation describes as **stealth models**, which are unreleased models made available for testing.

NVIDIA runs free endpoints too, for its Nemotron models, reachable through the NVIDIA API catalog. These are what OpenCode surfaces as `Nemotron 3 Ultra Free`, `Nemotron 3.5 Lightning Free` and `Nemotron 3.5 Super VL Free`.

You can fetch OpenCode's live list yourself rather than trusting a page that may be out of date:

```bash
curl https://opencode.ai/inference/v1/models
```

It is genuinely useful to be able to try a model that nobody has written a blog post about yet, at no cost, and to switch between models in the same agent without reconfiguring anything.

### The catch: what happens to your prompts

**Read this before you point an agent at a free endpoint with a real codebase open.**

The agent tools in this section work by reading your files and sending large parts of them to whichever model is answering. That means your source code, your configuration, and whatever else the agent decides to read is transmitted to a third party. With a free endpoint, it is very likely being logged, and in some cases retained and used to improve the model.

It is worth being precise about *which* free tiers do this, because the answer is not uniform and a blanket claim in either direction would be wrong.

OpenCode's own privacy documentation states that its providers generally follow a **zero-retention policy and do not use your data for model training**. Several of the free models are covered by that, explicitly: GLM-5.3-Flash Free, DeepSeek V4.1 Flash Free, Space Bunny Free and LongCat 2.5 Preview Free are listed as neither retaining your data nor training on it. Beam Free does not train on your data but is retained for 30 days for safety reasons.

But the documentation also lists explicit exceptions, and this is the part that matters:

- **Big Pickle, LL Alpha Free, Exo Free, MiMo-V2.6-Flash Free and MiMo-V2.5 Free** are each documented as: *"During its free period, collected data may be used to improve the model."* LL Alpha additionally states that data is retained for 30 days.
- **The NVIDIA free endpoints** are the most bluntly worded. The documentation marks them *"Trial use only — do not submit personal or confidential data"*, and states that your use is logged both for security purposes and **to improve NVIDIA products and services**. In fairness they do note that the session data logged for improvement is not linked to your identity or any persistent identifier, which limits the damage but does not change the instruction, which is not to send confidential data in the first place. Interacting with the endpoint constitutes consent to the NVIDIA API Trial Terms of Service.

There is a pattern worth noticing, and it is the actual answer to "is this safe". The models most likely to train on your data are the **stealth and preview models**, and the ones explicitly promised as zero-retention are the established ones. That is not an accident. Evaluating a model you have not released requires running it on real workloads, which is what "free" buys, and the established models on the free tier are the ones whose operators have made a commitment not to do it.

The practical rules that fall out of this:

- **Never point a free tier at code you cannot afford to have used as training data.** For an unreleased stealth model, assume everything it sees is fair game.
- **Never send credentials, tokens, `.env` files, customer data, or anything under an NDA** to any trial endpoint, including the ones documented as zero-retention. Zero retention is a policy, not a guarantee, and it will not cover a provider you did not check.
- **Treat the distinction as something to check per model**, because the list above changes as models are added and removed, and because "free" and "stealth" mean different things to different providers.
- **Read the terms yourself.** I have quoted the current documentation because policies change and because a guide written last month is not a source of truth. Every claim above is the provider's own statement as of now; yours is the version that applies when you press go.

The constructive answer to all of this is the one from section seven, and it is that the free tiers are for **learning the tools**, not for real work. Point the agent at a scratch repository, watch what it does, learn the failure modes. Then run it on anything you care about against your own `llama-server` from section five, which speaks the same API and keeps every byte on your own disk.

Which, incidentally, is the whole argument of this guide in one sentence. The tools are interchangeable with the model, and the model is the part with the privacy policy.

## Wrapping Up {#heading-12}

You now have the whole path, from a browser tab to a script running on your own machine.

Start at the hosted models, because they are free and they teach you the failure modes fastest. Move to `llama.cpp` with a small quantized model and get comfortable with quantization, context sizes, and what the timings on stderr actually mean. Then serve it, because the fact that a local model speaks the OpenAI API is the thing that makes all of this practical. Read the hardware section before you buy anything, since the model has to fit in memory and nothing else about your machine matters as much. Decide deliberately between local and cloud rather than defaulting to one. And only then add tools, on the smallest model that will do the job, keeping the tool list as short as the task allows.

Once the loop is comfortable, either keep going yourself or reach for one of the harnesses in section eleven and skip ahead. They are good tools and they will save you a lot of work, but you will debug them faster if you understand what they are doing, and the free tiers carry a data warning that only matters if you know what an agent is sending. Either way, keep the step limit low while you are learning.

The whole enterprise rests on one observation from the first section: the model emits text, and everything else is something you built around it. That is a limitation and it is also the whole design. When you find your agent doing something surprising, the answer is almost never that the model is clever. It is that you gave it a tool, gave it a file, and let it decide what to do with both.

Have fun, and keep the step limit low while you are learning.